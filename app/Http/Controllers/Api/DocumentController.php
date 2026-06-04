<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentChat;
use App\Services\DocumentTextExtractor;
use App\Services\GroqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentTextExtractor $extractor,
        private readonly GroqService $groq
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'message' => 'Guest belum memiliki dokumen tersimpan. Upload guest hanya berlaku di halaman saat ini.',
                'data' => [],
                'guest' => true,
            ]);
        }

        $search = $request->string('search')->toString();

        $documents = $this->documentQuery()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->limit((int) $request->integer('limit', 20))
            ->get();

        return response()->json([
            'message' => 'Daftar dokumen berhasil diambil.',
            'data' => $documents,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'subject' => ['required', 'string', 'max:150'],
            'file' => ['required_without:ocr_text', 'nullable', 'file', 'mimes:txt,pdf,docx,jpg,jpeg,png', 'max:10240'],
            'ocr_text' => ['required_without:file', 'nullable', 'string', 'min:20'],
        ], [
            'file.required_without' => 'Pilih file materi atau isi teks manual/OCR.',
            'ocr_text.required_without' => 'Isi teks manual/OCR jika tidak mengunggah file.',
            'ocr_text.min' => 'Teks manual/OCR minimal 20 karakter.',
            'file.mimes' => 'Format file harus txt, pdf, docx, jpg, jpeg, atau png.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        try {
            $extracted = $this->extractor->extractTextFromUploadedFile(
                $request->file('file'),
                $request->input('ocr_text')
            );
            
            $text = $extracted['text'];
            
            if (empty(trim($text))) {
                return response()->json([
                    'message' => 'Gagal mengunggah dokumen.',
                    'error' => 'Teks dokumen kosong atau gagal diekstrak.',
                ], 422);
            }
            
            $chapters = $this->extractor->detectChapters($text);

            $attributes = [
                'title' => $validated['title'],
                'subject' => $validated['subject'],
                'file_name' => $extracted['file_name'],
                'file_type' => $extracted['file_type'],
                'extracted_text' => $text,
                'chapters' => $chapters,
            ];

            if (! Auth::check()) {
                return response()->json([
                    'message' => 'Dokumen berhasil diekstrak sementara. Login untuk menyimpan dokumen dan riwayat.',
                    'data' => [
                        ...$attributes,
                        'id' => null,
                        '_id' => null,
                        'guest_upload' => true,
                    ],
                    'guest_upload' => true,
                ], 201);
            }

            $document = Document::query()->create([
                ...$attributes,
                'user_id' => (string) Auth::id(),
            ]);

            return response()->json([
                'message' => 'Dokumen berhasil diunggah dan diekstrak.',
                'data' => $document,
            ], 201);
            
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Gagal memproses dokumen.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(string $id): JsonResponse
    {
        $document = $this->documentQuery()->findOrFail($id);

        return response()->json([
            'message' => 'Detail dokumen berhasil diambil.',
            'data' => $document,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $document = $this->documentQuery()->findOrFail($id);
        $document->delete();
        
        // Hapus juga riwayat chat yang terkait
        DocumentChat::query()
            ->where('document_id', $id)
            ->when(Auth::check(), fn ($query) => $query->where('user_id', (string) Auth::id()))
            ->delete();

        return response()->json([
            'message' => 'Dokumen beserta riwayat chat berhasil dihapus.',
        ]);
    }

    public function chat(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:1000'],
            'chapter' => ['nullable', 'string', 'max:100'],
            'history' => ['nullable', 'array'],
        ]);

        $document = $this->documentQuery()->findOrFail($id);
        $textToUse = $this->getTextToUse($document, data_get($validated, 'chapter'), 9000);

        try {
            $history = data_get($validated, 'history', []);

            if ($this->groq->isQuizRequest($validated['message'])) {
                $counts = $this->groq->parseQuizRequest($validated['message']);
                $response = $this->groq->generateQuizFromDocument(
                    $textToUse,
                    data_get($validated, 'chapter'),
                    $counts['total_questions'],
                    $counts['question_type'],
                    $counts['multiple_choice_count'],
                    $counts['essay_count']
                );
                $chat = $this->saveChat($id, $validated['message'], $response, 'quiz');

                return response()->json([
                    'message' => 'Kuis berhasil dibuat dari chat dokumen.',
                    'data' => $chat,
                    'quiz' => $response,
                    'usage' => $this->groq->getLastUsage(),
                ]);
            }

            $response = $this->groq->chatWithDocument($textToUse, $validated['message'], data_get($validated, 'chapter'), $history);
            $chat = $this->saveChat($id, $validated['message'], $response, 'chat');
            
            return response()->json([
                'message' => 'Respons chat berhasil diterima.',
                'data' => $chat,
                'usage' => $this->groq->getLastUsage(),
            ]);
        } catch (Throwable $e) {
            return $this->aiError($e);
        }
    }

    public function summarize(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'chapter' => ['nullable', 'string', 'max:100'],
        ]);

        $document = $this->documentQuery()->findOrFail($id);
        $textToUse = $this->getTextToUse($document, data_get($validated, 'chapter'), 9500);

        try {
            $response = $this->groq->summarizeDocument($textToUse, data_get($validated, 'chapter'));
            $chat = $this->saveChat($id, 'Tolong ringkaskan materi ' . (data_get($validated, 'chapter') ?: 'ini'), $response, 'summarize');
            
            return response()->json([
                'message' => 'Ringkasan dokumen berhasil dibuat.',
                'data' => $chat,
                'summary' => $response,
                'usage' => $this->groq->getLastUsage(),
            ]);
        } catch (Throwable $e) {
            return $this->aiError($e);
        }
    }

    public function generateQuiz(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'chapter' => ['nullable', 'string', 'max:100'],
            'total_questions' => ['nullable', 'integer', 'min:1', 'max:25'],
            'question_type' => ['nullable', 'string', 'in:pilihan_ganda,essay,campuran'],
            'multiple_choice_count' => ['nullable', 'integer', 'min:0', 'max:25'],
            'essay_count' => ['nullable', 'integer', 'min:0', 'max:25'],
            'instruction' => ['nullable', 'string', 'max:500'],
        ]);

        $document = $this->documentQuery()->findOrFail($id);
        $textToUse = $this->getTextToUse($document, data_get($validated, 'chapter'), 4800);
        $totalQuestions = (int) data_get($validated, 'total_questions', 5);

        try {
            $questionType = (string) data_get($validated, 'question_type', 'pilihan_ganda');
            $counts = null;
            if (! blank(data_get($validated, 'instruction'))) {
                $counts = $this->groq->parseQuizRequest((string) data_get($validated, 'instruction'), $totalQuestions, $questionType);
            }

            $response = $this->groq->generateQuizFromDocument(
                $textToUse,
                data_get($validated, 'chapter'),
                (int) data_get($counts, 'total_questions', $totalQuestions),
                (string) data_get($counts, 'question_type', $questionType),
                data_get($counts, 'multiple_choice_count', data_get($validated, 'multiple_choice_count')),
                data_get($counts, 'essay_count', data_get($validated, 'essay_count'))
            );
            $prompt = data_get($validated, 'instruction') ?: "Buatkan $totalQuestions soal dari materi " . (data_get($validated, 'chapter') ?: 'ini');
            $chat = $this->saveChat($id, (string) $prompt, $response, 'quiz');
            
            return response()->json([
                'message' => 'Kuis berhasil dibuat.',
                'data' => $chat,
                'quiz' => $response,
                'usage' => $this->groq->getLastUsage(),
            ]);
        } catch (Throwable $e) {
            return $this->aiError($e);
        }
    }

    public function generateStudyPlan(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'chapter' => ['nullable', 'string', 'max:100'],
        ]);

        $document = $this->documentQuery()->findOrFail($id);
        $textToUse = $this->getTextToUse($document, data_get($validated, 'chapter'), 8500);

        try {
            $response = $this->groq->generateStudyPlanFromDocument($textToUse, data_get($validated, 'chapter'));
            $chat = $this->saveChat($id, "Buatkan rencana belajar dari materi " . (data_get($validated, 'chapter') ?: 'ini'), $response, 'study_plan');
            
            return response()->json([
                'message' => 'Rencana belajar berhasil dibuat.',
                'data' => $chat,
                'study_plan' => $response,
                'usage' => $this->groq->getLastUsage(),
            ]);
        } catch (Throwable $e) {
            return $this->aiError($e);
        }
    }

    public function history(string $id): JsonResponse
    {
        $chats = DocumentChat::query()
            ->where('document_id', $id)
            ->where('user_id', (string) Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Riwayat chat dokumen berhasil diambil.',
            'data' => $chats,
        ]);
    }
    
    private function getTextToUse(Document $document, ?string $chapterName, int $maxChars = 15000): string
    {
        $text = '';
        if (empty($chapterName) || $chapterName === 'Semua Materi') {
            $text = (string) $document->extracted_text;
        } else {
            $chapters = is_array($document->chapters) ? $document->chapters : [];
            foreach ($chapters as $chapter) {
                if (isset($chapter['chapter']) && $chapter['chapter'] === $chapterName) {
                    $text = $chapter['content'] ?? (string) $document->extracted_text;
                    break;
                }
            }
        }
        
        if (!$text) {
            $text = (string) $document->extracted_text;
        }
        
        $text = trim((string) $text);

        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        $head = (int) floor($maxChars * 0.78);
        $tail = max(500, $maxChars - $head - 160);

        return mb_substr($text, 0, $head)
            ."\n\n[Dokumen dipadatkan otomatis agar tetap masuk limit token Groq. Gunakan pilihan bab untuk hasil lebih detail.]\n\n"
            .mb_substr($text, -$tail);
    }

    private function documentQuery()
    {
        return Document::query()->where('user_id', (string) Auth::id());
    }

    /**
     * @param array|string $aiResponse
     */
    private function saveChat(string $documentId, string $userMessage, mixed $aiResponse, string $actionType): DocumentChat
    {
        $attributes = [
            'document_id' => $documentId,
            'user_message' => $userMessage,
            'ai_response' => is_string($aiResponse) ? $aiResponse : json_encode($aiResponse),
            'action_type' => $actionType,
            'provider' => 'Groq',
            'model' => (string) config('services.groq.model'),
        ];

        if (! Auth::check()) {
            return new DocumentChat($attributes);
        }

        $chat = DocumentChat::query()->create([
            ...$attributes,
            'user_id' => (string) Auth::id(),
        ]);

        return $chat;
    }

    private function aiError(Throwable $exception): JsonResponse
    {
        $error = $exception->getMessage();
        if (str_contains($error, 'terlalu besar') || str_contains($error, 'rate_limit') || str_contains($error, '413')) {
            $error = 'Materi terlalu panjang untuk limit token Groq saat ini. Coba ulangi setelah 1 menit, pilih bab tertentu, atau ringkas dokumen yang lebih fokus.';
        }

        return response()->json([
            'message' => 'AI belum bisa memproses permintaan.',
            'error' => $error,
        ], 422);
    }
}
