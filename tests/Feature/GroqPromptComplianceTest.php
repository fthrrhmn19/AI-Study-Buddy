<?php

namespace Tests\Feature;

use App\Services\GroqService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroqPromptComplianceTest extends TestCase
{
    public function test_chat_with_document_gives_more_completion_budget_for_maximal_prompt_and_keeps_tail_context(): void
    {
        config([
            'services.groq.key' => 'test-key',
            'services.groq.base_url' => 'https://groq.test/openai/v1',
            'services.groq.model' => 'test-model',
        ]);

        $captured = null;
        Http::fake(function ($request) use (&$captured) {
            $captured = $request->data();

            return Http::response([
                'choices' => [
                    ['message' => ['content' => 'Jawaban lengkap.']],
                ],
                'usage' => [
                    'prompt_tokens' => 100,
                    'completion_tokens' => 20,
                    'total_tokens' => 120,
                ],
                'model' => 'test-model',
            ]);
        });

        $longMaterial = str_repeat('Bagian awal materi tentang strategi bisnis digital. ', 260)
            .str_repeat('Bagian tengah materi tentang proses produksi konten. ', 80)
            .'BAGIAN_AKHIR_PENTING tentang manfaat laporan, portofolio, dan bukti kualitas layanan.';

        $service = new GroqService;
        $response = $service->chatWithDocument($longMaterial, 'Jelaskan seluruh materi ini dengan lengkap dan maksimal');

        $this->assertSame('Jawaban lengkap.', $response);
        $this->assertIsArray($captured);
        $this->assertSame(1800, data_get($captured, 'max_completion_tokens'));
        $this->assertStringContainsString('BAGIAN_AKHIR_PENTING', data_get($captured, 'messages.1.content'));
        $this->assertStringContainsString('Materi panjang dipadatkan otomatis', data_get($captured, 'messages.1.content'));
    }
}
