<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use App\Models\Document;
use App\Models\DocumentChat;
use App\Models\Material;
use App\Services\GroqService;
use App\Services\YouSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

class AiController extends Controller
{
    public function __construct(
        private readonly GroqService $groq,
        private readonly YouSearchService $youSearch
    ) {}

    public function summarize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required_without:material_id', 'nullable', 'string', 'max:120'],
            'subject' => ['required_without:material_id', 'nullable', 'string', 'max:80'],
            'content' => ['required_without:material_id', 'nullable', 'string', 'min:40'],
            'material_id' => ['nullable', 'string'],
            'save' => ['nullable', 'boolean'],
        ]);

        [$title, $subject, $content, $material] = $this->resolveMaterial($validated);

        // Enrich with You.com web search
        $webData = $this->fetchWebContext($title, $subject);

        try {
            $summary = $this->groq->summarize($title, $subject, $content, $webData['snippets']);
        } catch (Throwable $exception) {
            return $this->aiError($exception);
        }

        if (! $material && Auth::check() && (bool) data_get($validated, 'save', false)) {
            $material = Material::query()->create([
                'user_id' => (string) Auth::id(),
                'title' => $title,
                'subject' => $subject,
                'content' => $content,
                'created_by' => (string) config('services.developer.name'),
            ]);
        }

        $history = $this->saveHistory('summarize', 'Ringkas materi: '.$title, ['summary' => $summary], $material?->_id, [
            'source_context' => $content,
        ]);

        return response()->json([
            'message' => 'Ringkasan berhasil dibuat dengan Groq AI + You.com web enrichment.',
            'data' => [
                'summary' => $summary,
                'material' => $material,
                'history_id' => $history?->_id,
                'web_sources' => $webData['sources'],
            ],
            'usage' => $this->groq->getLastUsage(),
        ]);
    }

    public function quiz(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required_without:material_id', 'nullable', 'string', 'max:120'],
            'subject' => ['required_without:material_id', 'nullable', 'string', 'max:80'],
            'content' => ['required_without:material_id', 'nullable', 'string', 'min:40'],
            'material_id' => ['nullable', 'string'],
            'total_questions' => ['nullable', 'integer', 'min:1', 'max:25'],
            'question_type' => ['nullable', 'string', 'in:pilihan_ganda,essay,campuran'],
            'multiple_choice_count' => ['nullable', 'integer', 'min:0', 'max:25'],
            'essay_count' => ['nullable', 'integer', 'min:0', 'max:25'],
            'instruction' => ['nullable', 'string', 'max:500'],
        ]);

        [$title, $subject, $content, $material] = $this->resolveMaterial($validated);

        $webData = $this->fetchWebContext($title, $subject);

        try {
            $questionType = data_get($validated, 'question_type', 'pilihan_ganda');
            $counts = null;
            if (! blank(data_get($validated, 'instruction'))) {
                $counts = $this->groq->parseQuizRequest((string) data_get($validated, 'instruction'), (int) data_get($validated, 'total_questions', 5), $questionType);
            }

            $quiz = $this->groq->quiz(
                $title,
                $subject,
                $content,
                (int) data_get($counts, 'total_questions', data_get($validated, 'total_questions', 5)),
                $webData['snippets'],
                (string) data_get($counts, 'question_type', $questionType),
                data_get($counts, 'multiple_choice_count', data_get($validated, 'multiple_choice_count')),
                data_get($counts, 'essay_count', data_get($validated, 'essay_count'))
            );
        } catch (Throwable $exception) {
            return $this->aiError($exception);
        }

        $prompt = data_get($validated, 'instruction') ?: 'Buat kuis dari materi: '.$title;
        $history = $this->saveHistory('quiz', (string) $prompt, $quiz, $material?->_id, [
            'source_context' => $content,
        ]);

        return response()->json([
            'message' => 'Quiz berhasil dibuat dengan Groq AI + You.com web enrichment.',
            'data' => [
                'quiz' => $quiz,
                'material' => $material?->fresh(),
                'history_id' => $history?->_id,
                'web_sources' => $webData['sources'],
            ],
            'usage' => $this->groq->getLastUsage(),
        ]);
    }

    public function studyPlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required_without:material_id', 'nullable', 'string', 'max:120'],
            'subject' => ['required_without:material_id', 'nullable', 'string', 'max:80'],
            'content' => ['required_without:material_id', 'nullable', 'string', 'min:40'],
            'material_id' => ['nullable', 'string'],
            'deadline' => ['required', 'date'],
        ]);

        [$title, $subject, $content, $material] = $this->resolveMaterial($validated);

        $webData = $this->fetchWebContext($title, $subject);

        try {
            $plan = $this->groq->studyPlan($title, $subject, $content, (string) $validated['deadline'], $webData['snippets']);
        } catch (Throwable $exception) {
            return $this->aiError($exception);
        }

        $history = $this->saveHistory('study_plan', 'Buat rencana belajar: '.$title, $plan, $material?->_id, [
            'source_context' => $content,
        ]);

        return response()->json([
            'message' => 'Rencana belajar berhasil dibuat dengan Groq AI + You.com web enrichment.',
            'data' => [
                'study_plan' => $plan,
                'material' => $material?->fresh(),
                'history_id' => $history?->_id,
                'web_sources' => $webData['sources'],
            ],
            'usage' => $this->groq->getLastUsage(),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $limit = (int) $request->integer('limit', 20);
        $userId = $this->currentUserId();

        $currentAiGenerations = $this->aiHistoryQuery()
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    '_id' => $item->_id,
                    'type' => $item->type,
                    'provider' => $item->provider,
                    'model' => $item->model,
                    'prompt' => $item->prompt,
                    'result' => $item->result,
                    'created_at' => $item->created_at,
                    'source' => 'manual',
                ];
            });

        $legacyAiGenerations = $this->legacyAiHistoryQuery()
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    '_id' => $item->_id,
                    'type' => $item->type,
                    'provider' => $item->provider,
                    'model' => $item->model,
                    'prompt' => $item->prompt,
                    'result' => $item->result,
                    'created_at' => $item->created_at,
                    'source' => 'manual_legacy',
                ];
            });

        $documentChats = DocumentChat::query()
            ->where('user_id', $userId)
            ->whereIn('action_type', ['chat', 'summarize', 'summary', 'quiz', 'study_plan'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    '_id' => $item->_id,
                    'type' => $item->action_type === 'summary' ? 'summarize' : $item->action_type,
                    'provider' => $item->provider,
                    'model' => $item->model,
                    'prompt' => $item->user_message,
                    'result' => $item->ai_response,
                    'created_at' => $item->created_at,
                    'source' => 'document',
                ];
            });

        $history = $currentAiGenerations
            ->concat($legacyAiGenerations)
            ->concat($documentChats)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();

        return response()->json([
            'message' => 'Riwayat AI berhasil diambil.',
            'data' => $history,
        ]);
    }

    public function deleteHistory(string $id): JsonResponse
    {
        $item = $this->aiHistoryQuery()
            ->where('user_id', $this->currentUserId())
            ->where('_id', $id)
            ->first();
        if (! $item) {
            $item = $this->legacyAiHistoryQuery()
                ->where('user_id', $this->currentUserId())
                ->where('_id', $id)
                ->first();
        }

        if ($item) {
            $item->delete();
        } else {
            $docChat = DocumentChat::query()
                ->where('user_id', $this->currentUserId())
                ->where('_id', $id)
                ->first();
            if ($docChat) {
                $docChat->delete();
            } else {
                return response()->json(['message' => 'Riwayat tidak ditemukan.'], 404);
            }
        }

        return response()->json([
            'message' => 'Riwayat berhasil dihapus.',
        ]);
    }

    public function showHistory(string $id): JsonResponse
    {
        $item = $this->findHistoryItem($id);

        if (! $item) {
            return response()->json(['message' => 'Riwayat tidak ditemukan.'], 404);
        }

        return response()->json([
            'message' => 'Detail riwayat berhasil diambil.',
            'data' => $item,
        ]);
    }

    public function continueHistory(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:1000'],
            'content' => ['nullable', 'string', 'min:20'],
        ]);

        $item = $this->findHistoryItem($id);
        if (! $item) {
            return response()->json(['message' => 'Riwayat tidak ditemukan.'], 404);
        }

        $message = (string) $validated['message'];
        $previousAnswer = $this->historyResultText($item['result'] ?? '');
        $rawSourceContext = $this->resolveHistorySourceContext($item, (string) ($validated['content'] ?? ''));
        $sourceContext = $rawSourceContext;
        if (blank($sourceContext)) {
            $sourceContext = "PROMPT SEBELUMNYA:\n".(string) data_get($item, 'prompt', '')."\n\nJAWABAN SEBELUMNYA:\n".$previousAnswer;
        }

        $sourceContext = $this->compactSourceContext($sourceContext, $this->sourceContextPromptBudget($message));
        $conversationId = (string) (data_get($item, 'conversation_id') ?: $id);

        try {
            if ($this->groq->isQuizRequest($message)) {
                $counts = $this->groq->parseQuizRequest($message);
                $quiz = $this->groq->quiz(
                    'Lanjutan Riwayat',
                    (string) data_get($item, 'type', 'Materi'),
                    $sourceContext,
                    $counts['total_questions'],
                    '',
                    $counts['question_type'],
                    $counts['multiple_choice_count'],
                    $counts['essay_count']
                );

                $history = $this->saveHistory('quiz', $message, $quiz, data_get($item, 'material_id'), [
                    'source_context' => $rawSourceContext ?: $sourceContext,
                    'parent_id' => $id,
                    'conversation_id' => $conversationId,
                ]);

                return response()->json([
                    'message' => 'Lanjutan riwayat berhasil dibuat.',
                    'data' => [
                        'quiz' => $quiz,
                        'ai_response' => json_encode($quiz),
                        'history_id' => $history?->_id,
                        'conversation_id' => $conversationId,
                    ],
                    'usage' => $this->groq->getLastUsage(),
                ]);
            }

            $response = $this->groq->chatWithDocument($sourceContext, $message, null, [
                ['role' => 'user', 'content' => (string) data_get($item, 'prompt', '')],
                ['role' => 'assistant', 'content' => $previousAnswer],
            ]);
        } catch (Throwable $exception) {
            return $this->aiError($exception);
        }

        $history = $this->saveHistory('chat', $message, ['ai_response' => $response], data_get($item, 'material_id'), [
            'source_context' => $rawSourceContext ?: $sourceContext,
            'parent_id' => $id,
            'conversation_id' => $conversationId,
        ]);

        return response()->json([
            'message' => 'Lanjutan riwayat berhasil dibuat.',
            'data' => [
                'ai_response' => $response,
                'history_id' => $history?->_id,
                'conversation_id' => $conversationId,
            ],
            'usage' => $this->groq->getLastUsage(),
        ]);
    }

    public function deleteAllHistory(): JsonResponse
    {
        $this->aiHistoryQuery()->where('user_id', $this->currentUserId())->delete();
        $this->legacyAiHistoryQuery()->where('user_id', $this->currentUserId())->delete();
        DocumentChat::query()
            ->where('user_id', $this->currentUserId())
            ->whereIn('action_type', ['chat', 'summarize', 'summary', 'quiz', 'study_plan'])
            ->delete();

        return response()->json([
            'message' => 'Semua riwayat AI berhasil dihapus.',
        ]);
    }

    public function chatWithContent(Request $request): JsonResponse
    {
        $request->validate([
            'content' => ['required', 'string', 'min:20'],
            'message' => ['required', 'string', 'min:2'],
            'history' => ['nullable', 'array'],
            'history_id' => ['nullable', 'string'],
        ]);

        try {
            $message = (string) $request->input('message');
            if ($this->groq->isQuizRequest($message)) {
                $counts = $this->groq->parseQuizRequest($message);
                $quiz = $this->groq->quiz(
                    'Materi Chat',
                    'Materi',
                    (string) $request->input('content'),
                    $counts['total_questions'],
                    '',
                    $counts['question_type'],
                    $counts['multiple_choice_count'],
                    $counts['essay_count']
                );

                $history = $this->saveHistory('quiz', $message, $quiz, null, [
                    'source_context' => (string) $request->input('content'),
                    'parent_id' => $request->input('history_id'),
                ]);

                return response()->json([
                    'message' => 'Kuis berhasil dibuat dari chat.',
                    'data' => [
                        'quiz' => $quiz,
                        'ai_response' => json_encode($quiz),
                        'history_id' => $history?->_id,
                    ],
                    'usage' => $this->groq->getLastUsage(),
                ]);
            }

            $history = $request->input('history', []);
            if ($request->filled('history_id') && ($previous = $this->findHistoryItem((string) $request->input('history_id')))) {
                $history[] = ['role' => 'user', 'content' => (string) data_get($previous, 'prompt', '')];
                $history[] = ['role' => 'assistant', 'content' => $this->historyResultText(data_get($previous, 'result', ''))];
            }

            $response = $this->groq->chatWithDocument(
                $this->compactSourceContext((string) $request->input('content'), 6500),
                $message,
                null,
                is_array($history) ? $history : []
            );
        } catch (Throwable $exception) {
            return $this->aiError($exception);
        }

        $history = $this->saveHistory('chat', (string) $request->input('message'), ['ai_response' => $response], null, [
            'source_context' => (string) $request->input('content'),
            'parent_id' => $request->input('history_id'),
        ]);

        return response()->json([
            'message' => 'Berhasil.',
            'data' => [
                'ai_response' => $response,
                'history_id' => $history?->_id,
            ],
            'usage' => $this->groq->getLastUsage(),
        ]);
    }

    public function webSearch(Request $request): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'min:2'],
        ]);

        $results = $this->youSearch->search($request->input('query'), 5);

        return response()->json([
            'message' => 'Hasil pencarian web dari You.com.',
            'data' => $results,
            'configured' => $this->youSearch->isConfigured(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{0:string,1:string,2:string,3:?Material}
     */
    private function resolveMaterial(array $payload): array
    {
        $material = null;

        if (! empty($payload['material_id'])) {
            $material = Material::query()
                ->where('user_id', (string) Auth::id())
                ->findOrFail((string) $payload['material_id']);

            return [
                (string) $material->title,
                (string) $material->subject,
                (string) $material->content,
                $material,
            ];
        }

        return [
            (string) $payload['title'],
            (string) $payload['subject'],
            (string) $payload['content'],
            null,
        ];
    }

    /**
     * Fetch web context from You.com Search API.
     *
     * @return array{snippets: string, sources: array}
     */
    private function fetchWebContext(string $title, string $subject): array
    {
        if (! $this->youSearch->isConfigured()) {
            return ['snippets' => '', 'sources' => []];
        }

        $query = $this->youSearch->buildSearchQuery($title, $subject);

        return $this->youSearch->search($query);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    /**
     * @param  array<string, mixed>  $extra
     */
    private function saveHistory(string $type, string $input, array $result, mixed $materialId = null, array $extra = []): ?AiGeneration
    {
        if (! Auth::check()) {
            return null;
        }

        return $this->aiHistoryQuery()->create([
            'user_id' => $this->currentUserId(),
            'type' => $type,
            'provider' => 'Groq',
            'model' => (string) config('services.groq.model'),
            'prompt' => $input,
            'result' => json_encode($result, JSON_UNESCAPED_UNICODE),
            'material_id' => $materialId ? (string) $materialId : null,
            'conversation_id' => (string) data_get($extra, 'conversation_id', Str::uuid()),
            'parent_id' => data_get($extra, 'parent_id') ? (string) data_get($extra, 'parent_id') : null,
            'source_context' => data_get($extra, 'source_context') ? $this->compactSourceContext((string) data_get($extra, 'source_context'), 30000) : null,
            'metadata' => data_get($extra, 'metadata', []),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findHistoryItem(string $id): ?array
    {
        $item = $this->aiHistoryQuery()
            ->where('user_id', $this->currentUserId())
            ->where('_id', $id)
            ->first();

        if ($item) {
            return $this->formatAiHistoryItem($item, 'manual');
        }

        $legacyItem = $this->legacyAiHistoryQuery()
            ->where('user_id', $this->currentUserId())
            ->where('_id', $id)
            ->first();

        if ($legacyItem) {
            return $this->formatAiHistoryItem($legacyItem, 'manual_legacy');
        }

        $docChat = DocumentChat::query()
            ->where('user_id', $this->currentUserId())
            ->where('_id', $id)
            ->first();

        if ($docChat) {
            return $this->formatDocumentHistoryItem($docChat);
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatAiHistoryItem(AiGeneration $item, string $source): array
    {
        return [
            '_id' => $item->_id,
            'type' => $item->type,
            'provider' => $item->provider,
            'model' => $item->model,
            'prompt' => $item->prompt,
            'result' => $item->result,
            'material_id' => $item->material_id,
            'conversation_id' => $item->conversation_id,
            'parent_id' => $item->parent_id,
            'source_context' => $item->source_context ?: $this->materialSourceContext($item->material_id),
            'metadata' => $item->metadata,
            'created_at' => $item->created_at,
            'source' => $source,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatDocumentHistoryItem(DocumentChat $item): array
    {
        $document = Document::query()
            ->where('user_id', $this->currentUserId())
            ->where('_id', $item->document_id)
            ->first();

        return [
            '_id' => $item->_id,
            'type' => $item->action_type === 'summary' ? 'summarize' : $item->action_type,
            'provider' => $item->provider,
            'model' => $item->model,
            'prompt' => $item->user_message,
            'result' => $item->ai_response,
            'material_id' => null,
            'document_id' => $item->document_id,
            'conversation_id' => (string) $item->_id,
            'parent_id' => null,
            'source_context' => $document ? $this->compactSourceContext((string) $document->extracted_text, 30000) : null,
            'created_at' => $item->created_at,
            'source' => 'document',
        ];
    }

    private function resolveHistorySourceContext(array $item, string $requestContent = ''): string
    {
        if (! blank($requestContent)) {
            return $requestContent;
        }

        $materialContext = $this->materialSourceContext(data_get($item, 'material_id'));
        if (! blank($materialContext)) {
            return $materialContext;
        }

        if (data_get($item, 'document_id')) {
            $document = Document::query()
                ->where('user_id', $this->currentUserId())
                ->where('_id', data_get($item, 'document_id'))
                ->first();

            if ($document && ! blank($document->extracted_text)) {
                return (string) $document->extracted_text;
            }
        }

        if (! blank(data_get($item, 'source_context'))) {
            return (string) data_get($item, 'source_context');
        }

        if (data_get($item, 'parent_id') && ($parent = $this->findHistoryItem((string) data_get($item, 'parent_id')))) {
            $parentContext = $this->resolveHistorySourceContext($parent);
            if (! blank($parentContext)) {
                return $parentContext;
            }
        }

        if (data_get($item, 'conversation_id')) {
            $root = $this->aiHistoryQuery()
                ->where('user_id', $this->currentUserId())
                ->where('conversation_id', (string) data_get($item, 'conversation_id'))
                ->whereNotNull('source_context')
                ->oldest()
                ->first();

            if ($root && ! blank($root->source_context)) {
                return (string) $root->source_context;
            }
        }

        return '';
    }

    private function materialSourceContext(mixed $materialId): string
    {
        if (blank($materialId)) {
            return '';
        }

        $material = Material::query()
            ->where('user_id', $this->currentUserId())
            ->where('_id', (string) $materialId)
            ->first();

        return $material ? (string) $material->content : '';
    }

    private function historyResultText(mixed $result): string
    {
        if (is_array($result)) {
            $decoded = $result;
        } elseif (is_string($result)) {
            $decoded = json_decode($result, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return mb_substr($result, 0, 8000);
            }
        } else {
            return '';
        }

        if (isset($decoded['summary'])) {
            return (string) $decoded['summary'];
        }

        if (isset($decoded['ai_response'])) {
            return (string) $decoded['ai_response'];
        }

        if (isset($decoded['questions']) && is_array($decoded['questions'])) {
            $lines = [];
            foreach ($decoded['questions'] as $index => $question) {
                if (! is_array($question)) {
                    continue;
                }
                $lines[] = ($index + 1).'. '.(string) data_get($question, 'question', '-');
                if (data_get($question, 'answer')) {
                    $lines[] = 'Jawaban: '.(string) data_get($question, 'answer');
                }
                if (data_get($question, 'answer_key')) {
                    $lines[] = 'Kunci: '.(string) data_get($question, 'answer_key');
                }
            }

            return mb_substr(implode("\n", $lines), 0, 8000);
        }

        if (isset($decoded['goal'])) {
            return mb_substr(json_encode($decoded, JSON_UNESCAPED_UNICODE) ?: '', 0, 8000);
        }

        return mb_substr(json_encode($decoded, JSON_UNESCAPED_UNICODE) ?: '', 0, 8000);
    }

    private function compactSourceContext(string $text, int $maxChars): string
    {
        $text = trim($text);

        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        $head = (int) floor($maxChars * 0.78);
        $tail = max(500, $maxChars - $head - 160);

        return mb_substr($text, 0, $head)
            ."\n\n[Materi panjang dipadatkan otomatis agar sesuai limit token Groq.]\n\n"
            .mb_substr($text, -$tail);
    }

    private function sourceContextPromptBudget(string $message): int
    {
        $text = mb_strtolower($message);

        if (preg_match('/\b(maksimal|lengkap|detail|rinci|semua|menyeluruh|komprehensif|full|panjang)\b/iu', $text)) {
            return 9500;
        }

        if (preg_match('/\b(lebih\s*dikit|lebih\s*sedikit|lebih\s*singkat|singkat|pendek|padat|ringkas)\b/iu', $text)) {
            return 8500;
        }

        return 7600;
    }

    private function aiHistoryQuery()
    {
        return (new AiGeneration)->newQuery();
    }

    private function legacyAiHistoryQuery()
    {
        $legacyTable = (string) config('services.ai_history.legacy_collection', 'ai_histories');

        return (new AiGeneration)->setTable($legacyTable)->newQuery();
    }

    private function currentUserId(): string
    {
        return (string) Auth::id();
    }

    private function aiError(Throwable $exception): JsonResponse
    {
        $error = $exception->getMessage();
        if (str_contains($error, 'terlalu besar') || str_contains($error, 'rate_limit') || str_contains($error, '413')) {
            $error = 'Materi terlalu panjang untuk limit token Groq saat ini. Coba ulangi setelah 1 menit, pilih bab tertentu, atau gunakan materi yang lebih fokus.';
        }

        return response()->json([
            'message' => 'Fitur AI belum berhasil dijalankan.',
            'error' => $error,
            'hint' => 'Cek GROQ_API_KEY, koneksi internet, model Groq, dan environment variables di .env atau VPS.',
        ], 422);
    }
}
