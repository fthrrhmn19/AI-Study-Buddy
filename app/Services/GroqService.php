<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GroqService
{
    public function summarize(string $title, string $subject, string $content, string $webContext = ''): string
    {
        $content = $this->trimForPrompt($content, 8600);
        $webContext = $this->trimForPrompt($webContext, 800);
        $webSection = $webContext ? "\n\nREFERENSI WEB TERKINI (gunakan untuk memperkaya jawaban):\n{$webContext}" : '';

        return $this->chat([
            [
                'role' => 'system',
                'content' => 'Kamu adalah AI Study Buddy untuk mahasiswa Indonesia. Buat jawaban akurat, rapi, dan sesuai instruksi user. Untuk materi panjang, cakup semua bagian utama secara seimbang, jangan hanya meringkas bagian awal. Jika user meminta ringkas, tetap padat tetapi lengkap. Jika ada referensi web, gunakan untuk memperkaya dan memvalidasi ringkasan.',
            ],
            [
                'role' => 'user',
                'content' => "Buat ringkasan belajar dari materi berikut.\n".
                    "Judul: {$title}\nMata kuliah/topik: {$subject}\n\n".
                    "Format wajib:\n1. Inti Materi\n2. Poin Penting\n3. Istilah Kunci\n4. Kesimpulan Singkat\n\nMateri:\n{$content}{$webSection}",
            ],
        ], 0.3, 1200);
    }

    public function quiz(
        string $title,
        string $subject,
        string $content,
        int $totalQuestions = 5,
        string $webContext = '',
        string $questionType = 'pilihan_ganda',
        ?int $multipleChoiceCount = null,
        ?int $essayCount = null
    ): array {
        [$pgCount, $essayTarget] = $this->resolveQuizCounts(
            $totalQuestions,
            $questionType,
            $multipleChoiceCount,
            $essayCount
        );

        return $this->generateStructuredQuiz($title, $subject, $content, $pgCount, $essayTarget, $webContext);
    }

    /**
     * @return array{total_questions:int,question_type:string,multiple_choice_count:int,essay_count:int}
     */
    public function parseQuizRequest(string $message, int $defaultTotal = 5, string $defaultType = 'campuran'): array
    {
        $text = mb_strtolower($message);
        $multipleChoice = $this->firstMatchedNumber($text, '/(\d+)\s*(?:soal\s*)?(?:pilihan\s*ganda|pg|multiple\s*choice)/u');
        $essay = $this->firstMatchedNumber($text, '/(\d+)\s*(?:soal\s*)?(?:essay|esai|uraian)/u');
        $total = $this->firstMatchedNumber($text, '/(\d+)\s*(?:soal|kuis|quiz)/u');

        if ($multipleChoice !== null || $essay !== null) {
            $multipleChoice = max(0, (int) ($multipleChoice ?? 0));
            $essay = max(0, (int) ($essay ?? 0));

            if ($multipleChoice === 0 && $essay === 0) {
                $multipleChoice = $defaultTotal;
            }

            $questionType = $multipleChoice > 0 && $essay > 0
                ? 'campuran'
                : ($essay > 0 ? 'essay' : 'pilihan_ganda');

            return [
                'total_questions' => min(25, $multipleChoice + $essay),
                'question_type' => $questionType,
                'multiple_choice_count' => min(25, $multipleChoice),
                'essay_count' => min(25, $essay),
            ];
        }

        $questionType = str_contains($text, 'essay') || str_contains($text, 'esai') || str_contains($text, 'uraian')
            ? 'essay'
            : (str_contains($text, 'campuran') ? 'campuran' : $defaultType);

        [$pgCount, $essayCount] = $this->resolveQuizCounts((int) ($total ?? $defaultTotal), $questionType);

        return [
            'total_questions' => $pgCount + $essayCount,
            'question_type' => $questionType,
            'multiple_choice_count' => $pgCount,
            'essay_count' => $essayCount,
        ];
    }

    public function isQuizRequest(string $message): bool
    {
        return (bool) preg_match('/\b(kuis|quiz|soal|pilihan\s*ganda|pg|essay|esai|uraian)\b/iu', $message);
    }

    public function studyPlan(string $title, string $subject, string $content, string $deadline, string $webContext = ''): array
    {
        $content = $this->trimForPrompt($content, 6500);
        $webContext = $this->trimForPrompt($webContext, 700);
        $webSection = $webContext ? "\n\nREFERENSI WEB (gunakan untuk memperkaya rencana):\n{$webContext}" : '';
        $raw = $this->chat([
            [
                'role' => 'system',
                'content' => 'Kamu adalah AI mentor belajar. Balas hanya JSON valid tanpa markdown. Jika ada referensi web, gunakan untuk membuat rencana belajar lebih efektif.',
            ],
            [
                'role' => 'user',
                'content' => "Buat rencana belajar sampai deadline {$deadline}.\n".
                    "Judul: {$title}\nTopik: {$subject}\n\n".
                    "Skema JSON wajib: {\"goal\":\"...\",\"steps\":[{\"day\":\"Hari 1\",\"task\":\"...\",\"duration\":\"...\"}],\"tips\":[\"...\"]}\n\n".
                    "Materi:\n{$content}{$webSection}",
            ],
        ], 0.25, 750);

        return $this->decodeJson($raw, ['goal' => 'Belajar materi secara bertahap', 'steps' => [], 'tips' => []]);
    }

    public function chatWithDocument(string $documentText, string $userMessage, ?string $chapter = null, array $history = []): string
    {
        $documentText = $this->trimForPrompt($documentText, $this->documentContextBudget($userMessage));
        $context = $chapter ? "BAB YANG DIMINTA: {$chapter}\n\n" : '';

        $messages = [
            [
                'role' => 'system',
                'content' => 'Kamu adalah AI Study Buddy untuk mahasiswa. Gunakan MATERI sebagai sumber utama dan sesuaikan panjang, format, dan kedalaman jawaban dengan prompt user. Jika user meminta ringkas ulang, revisi, lebih singkat, lebih jelas, lebih detail, maksimal, lengkap, semua poin, soal, atau rencana belajar, olah seluruh materi yang tersedia. Riwayat chat hanya dipakai sebagai konteks gaya/permintaan sebelumnya, bukan sebagai satu-satunya sumber jika materi asli tersedia. Jika prompt meminta singkat, buat padat tetapi tetap mencakup semua bagian utama. Jika prompt meminta lengkap/maksimal/detail, berikan jawaban lebih menyeluruh dengan struktur rapi. Jika user menanyakan fakta yang benar-benar tidak ada di materi, baru katakan informasi tidak ditemukan dalam dokumen.',
            ],
            [
                'role' => 'user',
                'content' => "MATERI:\n{$documentText}\n\n{$context}ATURAN WAJIB:\n- Jawab berdasarkan seluruh materi di atas, bukan hanya jawaban sebelumnya.\n- Patuhi instruksi user tentang format, jumlah, panjang, dan gaya.\n- Untuk ringkasan singkat, tetap sebutkan semua topik utama secara padat.\n- Untuk jawaban lengkap/maksimal, buat lebih detail dan terstruktur.\n\n",
            ],
        ];

        // Append chat history
        foreach (array_slice($history, -6) as $msg) {
            if (isset($msg['role']) && isset($msg['content'])) {
                $messages[] = [
                    'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                    'content' => $this->trimForPrompt((string) $msg['content'], 1200),
                ];
            }
        }

        // Append current message
        $messages[] = [
            'role' => 'user',
            'content' => "PERTANYAAN USER:\n{$userMessage}\n\nJAWABAN:",
        ];

        return $this->chat($messages, 0.3, $this->responseBudgetForUserMessage($userMessage));
    }

    public function summarizeDocument(string $documentText, ?string $chapter = null): string
    {
        $documentText = $this->trimForPrompt($documentText, 9000);
        $context = $chapter ? "BAB YANG DIMINTA: {$chapter}\n\n" : "BAB YANG DIMINTA: Semua Materi\n\n";

        return $this->chat([
            [
                'role' => 'system',
                'content' => 'Kamu adalah AI Study Buddy untuk mahasiswa. Ringkas materi kuliah dari dokumen/buku berikut secara akurat, rapi, dan mencakup seluruh bagian utama. Untuk materi panjang, jangan hanya mengambil bagian awal; rangkum bagian awal, tengah, dan akhir secara seimbang. Format: 1. Judul Materi, 2. Inti Pembahasan, 3. Poin-Poin Penting, 4. Istilah Penting, 5. Kesimpulan Singkat.',
            ],
            [
                'role' => 'user',
                'content' => "Jika user memilih bab tertentu, fokus hanya pada bab tersebut.\n\nMATERI:\n{$documentText}\n\n{$context}RINGKASAN:",
            ],
        ], 0.3, 1200);
    }

    public function generateQuizFromDocument(
        string $documentText,
        ?string $chapter = null,
        int $totalQuestions = 5,
        string $questionType = 'pilihan_ganda',
        ?int $multipleChoiceCount = null,
        ?int $essayCount = null
    ): array {
        $context = $chapter ? "BAB YANG DIMINTA: {$chapter}\n\n" : "BAB YANG DIMINTA: Semua Materi\n\n";

        [$pgCount, $essayTarget] = $this->resolveQuizCounts(
            $totalQuestions,
            $questionType,
            $multipleChoiceCount,
            $essayCount
        );

        return $this->generateStructuredQuiz(
            'Dokumen Materi',
            $chapter ?: 'Semua Materi',
            "{$context}{$documentText}",
            $pgCount,
            $essayTarget
        );
    }

    public function generateStudyPlanFromDocument(string $documentText, ?string $chapter = null): array
    {
        $documentText = $this->trimForPrompt($documentText, 7600);
        $context = $chapter ? "BAB YANG DIMINTA: {$chapter}\n\n" : "BAB YANG DIMINTA: Semua Materi\n\n";

        $raw = $this->chat([
            [
                'role' => 'system',
                'content' => 'Kamu adalah AI Study Buddy untuk mahasiswa. Buat rencana belajar terstruktur berdasarkan materi dokumen berikut. Skema JSON harus valid tanpa markdown tambahan.',
            ],
            [
                'role' => 'user',
                'content' => "Skema JSON wajib:\n".
                    '{"goal":"...","steps":[{"day":"Hari 1","task":"...","duration":"..."}],"tips":["..."]}'.
                    "\n\nMATERI:\n{$documentText}\n\n{$context}RENCANA BELAJAR:",
            ],
        ], 0.25, 750);

        return $this->decodeJson($raw, ['goal' => 'Memahami isi dokumen', 'steps' => [], 'tips' => []]);
    }

    /** @var array<string, mixed>|null */
    private ?array $lastUsage = null;

    /** @return array<string, mixed>|null */
    public function getLastUsage(): ?array
    {
        return $this->lastUsage;
    }

    /**
     * @return array{0:int,1:int}
     */
    private function resolveQuizCounts(
        int $totalQuestions,
        string $questionType = 'pilihan_ganda',
        ?int $multipleChoiceCount = null,
        ?int $essayCount = null
    ): array {
        if ($multipleChoiceCount !== null || $essayCount !== null) {
            $pgCount = max(0, (int) ($multipleChoiceCount ?? 0));
            $essayTarget = max(0, (int) ($essayCount ?? 0));

            if ($pgCount + $essayTarget > 0) {
                return $this->capQuizCounts($pgCount, $essayTarget);
            }
        }

        $totalQuestions = max(1, min($totalQuestions, 25));

        if ($questionType === 'essay') {
            return [0, $totalQuestions];
        }

        if ($questionType === 'campuran') {
            $pgCount = (int) ceil($totalQuestions / 2);

            return $this->capQuizCounts($pgCount, $totalQuestions - $pgCount);
        }

        return [$totalQuestions, 0];
    }

    /**
     * @return array{0:int,1:int}
     */
    private function capQuizCounts(int $pgCount, int $essayCount): array
    {
        $pgCount = max(0, $pgCount);
        $essayCount = max(0, $essayCount);

        if ($pgCount + $essayCount <= 25) {
            return [$pgCount, $essayCount];
        }

        $overflow = ($pgCount + $essayCount) - 25;
        if ($essayCount >= $overflow) {
            $essayCount -= $overflow;
        } else {
            $pgCount = max(0, $pgCount - ($overflow - $essayCount));
            $essayCount = 0;
        }

        return [$pgCount, $essayCount];
    }

    private function generateStructuredQuiz(
        string $title,
        string $subject,
        string $content,
        int $multipleChoiceCount,
        int $essayCount,
        string $webContext = ''
    ): array {
        $content = $this->trimForPrompt($content, $this->quizContentBudget($multipleChoiceCount, $essayCount));
        $webContext = $this->trimForPrompt($webContext, 600);
        $quiz = ['multiple_choice' => [], 'essay' => []];
        $raw = '';

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $raw = $this->chat($this->quizMessages($title, $subject, $content, $multipleChoiceCount, $essayCount, $webContext), 0.15, $this->quizTokenBudget($multipleChoiceCount, $essayCount));
            $decoded = $this->decodeJson($raw, ['multiple_choice' => [], 'essay' => []]);
            $quiz = $this->normalizeQuizPayload($decoded);

            if (count($quiz['multiple_choice']) >= $multipleChoiceCount && count($quiz['essay']) >= $essayCount) {
                break;
            }
        }

        $missingPg = max(0, $multipleChoiceCount - count($quiz['multiple_choice']));
        $missingEssay = max(0, $essayCount - count($quiz['essay']));

        if ($missingPg || $missingEssay) {
            $extra = $this->generateMissingQuizItems($title, $subject, $content, $missingPg, $missingEssay, $quiz);
            $quiz['multiple_choice'] = array_merge($quiz['multiple_choice'], $extra['multiple_choice']);
            $quiz['essay'] = array_merge($quiz['essay'], $extra['essay']);
        }

        $questions = array_merge(
            array_slice($quiz['multiple_choice'], 0, $multipleChoiceCount),
            array_slice($quiz['essay'], 0, $essayCount)
        );

        $result = [
            'questions' => $questions,
            'meta' => [
                'requested_multiple_choice' => $multipleChoiceCount,
                'requested_essay' => $essayCount,
                'generated_multiple_choice' => min(count($quiz['multiple_choice']), $multipleChoiceCount),
                'generated_essay' => min(count($quiz['essay']), $essayCount),
            ],
        ];

        if (count($questions) !== $multipleChoiceCount + $essayCount) {
            $result['warning'] = 'Jumlah soal belum sesuai permintaan karena AI tidak mengembalikan struktur lengkap. Coba ulangi dengan materi lebih pendek atau pilih bab tertentu.';
            $result['raw'] = $raw;
        }

        return $result;
    }

    /**
     * @return array<int, array{role:string, content:string}>
     */
    private function quizMessages(string $title, string $subject, string $content, int $multipleChoiceCount, int $essayCount, string $webContext = ''): array
    {
        $webSection = $webContext ? "\n\nREFERENSI WEB:\n{$webContext}" : '';

        return [
            [
                'role' => 'system',
                'content' => 'Kamu adalah pembuat soal ujian profesional. Balas HANYA JSON valid, tanpa markdown, tanpa teks pembuka. Patuhi jumlah soal secara tepat.',
            ],
            [
                'role' => 'user',
                'content' => "Buat quiz dari materi berikut.\n".
                    "Judul: {$title}\nTopik: {$subject}\n".
                    "Jumlah WAJIB tepat: {$multipleChoiceCount} pilihan ganda dan {$essayCount} essay.\n\n".
                    "Aturan wajib:\n".
                    "- Array multiple_choice HARUS berisi tepat {$multipleChoiceCount} item.\n".
                    "- Array essay HARUS berisi tepat {$essayCount} item.\n".
                    "- Setiap pilihan ganda punya 4 opsi A-D, satu jawaban, dan pembahasan singkat.\n".
                    "- Setiap essay punya kunci jawaban dan pembahasan singkat.\n".
                    "- Jika materi terbatas, variasikan sudut pertanyaan dari konsep yang sama, tetapi jumlah tetap tepat.\n\n".
                    "Skema JSON wajib:\n".
                    '{"multiple_choice":[{"question":"...","options":["A. ...","B. ...","C. ...","D. ..."],"answer":"A","explanation":"..."}],"essay":[{"question":"...","answer_key":"...","explanation":"..."}]}'.
                    "\n\nMATERI:\n{$content}{$webSection}",
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $existing
     * @return array{multiple_choice:array<int,array<string,mixed>>,essay:array<int,array<string,mixed>>}
     */
    private function generateMissingQuizItems(string $title, string $subject, string $content, int $missingPg, int $missingEssay, array $existing): array
    {
        if ($missingPg === 0 && $missingEssay === 0) {
            return ['multiple_choice' => [], 'essay' => []];
        }

        $raw = $this->chat([
            [
                'role' => 'system',
                'content' => 'Kamu melengkapi kekurangan soal quiz. Balas HANYA JSON valid sesuai schema, tanpa markdown.',
            ],
            [
                'role' => 'user',
                'content' => "Quiz sebelumnya masih kurang. Tambahkan tepat {$missingPg} pilihan ganda dan {$missingEssay} essay.\n".
                    "Jangan duplikasi soal berikut:\n".
                    json_encode($existing, JSON_UNESCAPED_UNICODE).
                    "\n\nJudul: {$title}\nTopik: {$subject}\nMateri:\n{$content}\n\n".
                    'Schema: {"multiple_choice":[{"question":"...","options":["A. ...","B. ...","C. ...","D. ..."],"answer":"A","explanation":"..."}],"essay":[{"question":"...","answer_key":"...","explanation":"..."}]}',
            ],
        ], 0.2, $this->quizTokenBudget($missingPg, $missingEssay));

        return $this->normalizeQuizPayload($this->decodeJson($raw, ['multiple_choice' => [], 'essay' => []]));
    }

    /**
     * @param  array<string, mixed>  $decoded
     * @return array{multiple_choice:array<int,array<string,mixed>>,essay:array<int,array<string,mixed>>}
     */
    private function normalizeQuizPayload(array $decoded): array
    {
        $multipleChoice = [];
        $essay = [];

        if (isset($decoded['questions']) && is_array($decoded['questions'])) {
            foreach ($decoded['questions'] as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $options = data_get($item, 'options', []);
                if (($item['type'] ?? '') === 'essay' || empty($options)) {
                    $normalized = $this->normalizeEssayQuestion($item);
                    if ($normalized) {
                        $essay[] = $normalized;
                    }
                } else {
                    $normalized = $this->normalizeMultipleChoiceQuestion($item);
                    if ($normalized) {
                        $multipleChoice[] = $normalized;
                    }
                }
            }
        }

        foreach (data_get($decoded, 'multiple_choice', []) as $item) {
            if (is_array($item) && ($normalized = $this->normalizeMultipleChoiceQuestion($item))) {
                $multipleChoice[] = $normalized;
            }
        }

        foreach (data_get($decoded, 'essay', []) as $item) {
            if (is_array($item) && ($normalized = $this->normalizeEssayQuestion($item))) {
                $essay[] = $normalized;
            }
        }

        return [
            'multiple_choice' => array_values($multipleChoice),
            'essay' => array_values($essay),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    private function normalizeMultipleChoiceQuestion(array $item): ?array
    {
        $question = trim((string) data_get($item, 'question', ''));
        $rawOptions = data_get($item, 'options', []);

        if ($question === '' || ! is_array($rawOptions) || count($rawOptions) < 4) {
            return null;
        }

        $labels = ['A', 'B', 'C', 'D'];
        $options = [];
        foreach (array_slice(array_values($rawOptions), 0, 4) as $index => $option) {
            $text = trim((string) $option);
            $options[] = preg_match('/^[A-D]\./i', $text) ? $text : $labels[$index].'. '.$text;
        }

        $answer = strtoupper(substr(trim((string) data_get($item, 'answer', 'A')), 0, 1));
        if (! in_array($answer, $labels, true)) {
            $answer = 'A';
        }

        return [
            'type' => 'multiple_choice',
            'question' => $question,
            'options' => $options,
            'answer' => $answer,
            'explanation' => trim((string) data_get($item, 'explanation', '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    private function normalizeEssayQuestion(array $item): ?array
    {
        $question = trim((string) data_get($item, 'question', ''));

        if ($question === '') {
            return null;
        }

        return [
            'type' => 'essay',
            'question' => $question,
            'answer_key' => trim((string) (data_get($item, 'answer_key') ?: data_get($item, 'answer', ''))),
            'explanation' => trim((string) data_get($item, 'explanation', '')),
        ];
    }

    private function quizTokenBudget(int $multipleChoiceCount, int $essayCount): int
    {
        return min(3200, max(1100, 560 + ($multipleChoiceCount * 190) + ($essayCount * 160)));
    }

    private function quizContentBudget(int $multipleChoiceCount, int $essayCount): int
    {
        $requested = $multipleChoiceCount + $essayCount;

        if ($requested >= 15) {
            return 4200;
        }

        if ($requested >= 10) {
            return 5200;
        }

        return 6500;
    }

    private function trimForPrompt(string $text, int $maxChars): string
    {
        $text = trim($text);

        return mb_strlen($text) > $maxChars ? $this->compactText($text, $maxChars) : $text;
    }

    private function documentContextBudget(string $userMessage): int
    {
        $text = mb_strtolower($userMessage);

        if (preg_match('/\b(maksimal|lengkap|detail|rinci|semua|menyeluruh|komprehensif|full|panjang)\b/iu', $text)) {
            return 9000;
        }

        if (preg_match('/\b(lebih\s*dikit|lebih\s*sedikit|lebih\s*singkat|singkat|pendek|padat|ringkas)\b/iu', $text)) {
            return 8200;
        }

        return 7600;
    }

    private function responseBudgetForUserMessage(string $userMessage): int
    {
        $text = mb_strtolower($userMessage);

        if (preg_match('/\b(maksimal|lengkap|detail|rinci|semua|menyeluruh|komprehensif|full|panjang)\b/iu', $text)) {
            return 1800;
        }

        if (preg_match('/\b(lebih\s*dikit|lebih\s*sedikit|lebih\s*singkat|singkat|pendek|padat|ringkas)\b/iu', $text)) {
            return 1150;
        }

        return 1350;
    }

    /**
     * @param  array<int, array{role:string, content:string}>  $messages
     * @return array{0:array<int, array{role:string, content:string}>,1:int}
     */
    private function fitRequestToGroqLimit(array $messages, int $maxCompletionTokens): array
    {
        $maxTotalTokens = 5800;
        $maxCompletionTokens = min($maxCompletionTokens, 3200);
        $promptTokens = $this->estimatePromptTokens($messages);

        if ($promptTokens + $maxCompletionTokens <= $maxTotalTokens) {
            return [$messages, $maxCompletionTokens];
        }

        $allowedPromptTokens = max(900, $maxTotalTokens - $maxCompletionTokens);
        $allowedPromptChars = max(3000, $allowedPromptTokens * 3);
        $messages = $this->compactMessages($messages, $allowedPromptChars);
        $promptTokens = $this->estimatePromptTokens($messages);

        if ($promptTokens + $maxCompletionTokens > $maxTotalTokens) {
            $maxCompletionTokens = max(650, $maxTotalTokens - $promptTokens);
        }

        return [$messages, $maxCompletionTokens];
    }

    /**
     * @param  array<int, array{role:string, content:string}>  $messages
     */
    private function estimatePromptTokens(array $messages): int
    {
        $chars = 0;
        foreach ($messages as $message) {
            $chars += mb_strlen((string) ($message['content'] ?? ''));
        }

        return (int) ceil(($chars / 3.6) + (count($messages) * 5));
    }

    /**
     * @param  array<int, array{role:string, content:string}>  $messages
     * @return array<int, array{role:string, content:string}>
     */
    private function compactMessages(array $messages, int $maxTotalChars): array
    {
        $systemBudget = 900;
        $systemChars = 0;
        $userIndexes = [];

        foreach ($messages as $index => $message) {
            if (($message['role'] ?? '') === 'system') {
                $messages[$index]['content'] = $this->compactText((string) $message['content'], $systemBudget);
                $systemChars += mb_strlen((string) $messages[$index]['content']);
            } else {
                $userIndexes[] = $index;
            }
        }

        if (! $userIndexes) {
            return $messages;
        }

        $remaining = max(1800, $maxTotalChars - $systemChars);
        $materialIndexes = [];
        foreach ($userIndexes as $index) {
            if (str_contains((string) ($messages[$index]['content'] ?? ''), 'MATERI:')) {
                $materialIndexes[] = $index;
            }
        }

        $lastUserIndex = end($userIndexes);
        $historyIndexes = array_values(array_filter(
            $userIndexes,
            fn (int $index): bool => ! in_array($index, $materialIndexes, true) && $index !== $lastUserIndex
        ));

        $materialBudget = $materialIndexes ? max(1800, (int) floor($remaining * 0.58)) : 0;
        $currentBudget = $lastUserIndex !== false ? max(700, (int) floor($remaining * 0.18)) : 0;
        $historyBudget = max(0, $remaining - $materialBudget - $currentBudget);
        $historyPerMessage = $historyIndexes ? max(450, (int) floor($historyBudget / count($historyIndexes))) : 0;

        foreach ($userIndexes as $index) {
            if (in_array($index, $materialIndexes, true)) {
                $perMaterial = max(1400, (int) floor($materialBudget / max(1, count($materialIndexes))));
                $messages[$index]['content'] = $this->compactText((string) $messages[$index]['content'], $perMaterial);

                continue;
            }

            if ($index === $lastUserIndex) {
                $messages[$index]['content'] = $this->compactText((string) $messages[$index]['content'], $currentBudget);

                continue;
            }

            $messages[$index]['content'] = $this->compactText((string) $messages[$index]['content'], $historyPerMessage ?: 600);
        }

        return $messages;
    }

    private function compactText(string $text, int $maxChars): string
    {
        $text = trim($text);

        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        $head = (int) floor($maxChars * 0.72);
        $tail = max(300, $maxChars - $head - 120);

        return mb_substr($text, 0, $head)
            ."\n\n[Materi panjang dipadatkan otomatis agar sesuai limit token Groq.]\n\n"
            .mb_substr($text, -$tail);
    }

    private function firstMatchedNumber(string $text, string $pattern): ?int
    {
        if (preg_match($pattern, $text, $match)) {
            return (int) $match[1];
        }

        return null;
    }

    /**
     * @param  array<int, array{role:string, content:string}>  $messages
     */
    private function chat(array $messages, float $temperature = 0.3, int $maxCompletionTokens = 1200): string
    {
        $apiKey = config('services.groq.key');
        $baseUrl = rtrim((string) config('services.groq.base_url'), '/');
        $model = (string) config('services.groq.model');

        if (blank($apiKey) || $apiKey === 'isi_api_key_groq_kamu') {
            throw new RuntimeException('GROQ_API_KEY belum diisi di file .env atau Environment Variables.');
        }

        [$messages, $maxCompletionTokens] = $this->fitRequestToGroqLimit($messages, $maxCompletionTokens);

        try {
            $response = Http::timeout(45)
                ->acceptJson()
                ->withToken($apiKey)
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'max_completion_tokens' => $maxCompletionTokens,
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Gagal terhubung ke Groq API: '.$exception->getMessage());
        }

        if ($response->status() === 429) {
            throw new RuntimeException('Limit token Groq sedang penuh. Tunggu sekitar 1 menit, pilih bab tertentu, atau coba lagi dengan dokumen yang lebih pendek.');
        }

        if ($response->status() === 413 || str_contains($response->body(), 'rate_limit_exceeded')) {
            throw new RuntimeException('Permintaan terlalu besar untuk limit token Groq saat ini. Sistem sudah memadatkan dokumen, tetapi model masih membatasi kapasitas. Coba ulangi setelah 1 menit, pilih bab tertentu, atau gunakan materi yang lebih fokus.');
        }

        if ($response->failed()) {
            throw new RuntimeException('AI belum bisa memproses permintaan. Status Groq: '.$response->status().'. Coba ulangi beberapa saat lagi atau periksa API key/koneksi.');
        }

        $json = $response->json();

        // Capture token usage + rate limit headers
        $this->lastUsage = [
            'prompt_tokens' => data_get($json, 'usage.prompt_tokens', 0),
            'completion_tokens' => data_get($json, 'usage.completion_tokens', 0),
            'total_tokens' => data_get($json, 'usage.total_tokens', 0),
            'model' => data_get($json, 'model', $model),
            'limit_tokens' => (int) $response->header('x-ratelimit-limit-tokens', 0),
            'remaining_tokens' => (int) $response->header('x-ratelimit-remaining-tokens', 0),
            'limit_requests' => (int) $response->header('x-ratelimit-limit-requests', 0),
            'remaining_requests' => (int) $response->header('x-ratelimit-remaining-requests', 0),
        ];

        return (string) data_get($json, 'choices.0.message.content', '');
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $raw, array $fallback): array
    {
        $clean = trim($raw);

        if (str_starts_with($clean, '```')) {
            $clean = preg_replace('/^```(?:json)?|```$/m', '', $clean) ?? $clean;
        }

        if (preg_match('/\{.*\}/s', $clean, $match)) {
            $clean = $match[0];
        }

        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return array_merge($fallback, [
                'raw' => $raw,
                'warning' => 'AI tidak mengembalikan JSON valid. Raw response disimpan untuk evaluasi.',
            ]);
        }

        return $decoded;
    }
}
