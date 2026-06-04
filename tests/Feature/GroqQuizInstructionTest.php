<?php

namespace Tests\Feature;

use App\Services\GroqService;
use Tests\TestCase;

class GroqQuizInstructionTest extends TestCase
{
    public function test_it_parses_mixed_quiz_counts_from_user_instruction(): void
    {
        $service = new GroqService;

        $parsed = $service->parseQuizRequest('buat 10 pilihan ganda dan 5 essay');

        $this->assertSame(15, $parsed['total_questions']);
        $this->assertSame('campuran', $parsed['question_type']);
        $this->assertSame(10, $parsed['multiple_choice_count']);
        $this->assertSame(5, $parsed['essay_count']);
    }

    public function test_it_keeps_separate_counts_when_user_asks_for_split_numbering(): void
    {
        $service = new GroqService;

        $parsed = $service->parseQuizRequest('buat 10 pilihan ganda dan 5 essay dipisah pilihan ganda 1-10 dan essay 1-5');

        $this->assertSame(15, $parsed['total_questions']);
        $this->assertSame('campuran', $parsed['question_type']);
        $this->assertSame(10, $parsed['multiple_choice_count']);
        $this->assertSame(5, $parsed['essay_count']);
    }

    public function test_it_parses_single_type_quiz_counts(): void
    {
        $service = new GroqService;

        $multipleChoice = $service->parseQuizRequest('tolong buat 12 pilihan ganda');
        $essay = $service->parseQuizRequest('buatkan 4 essay');

        $this->assertSame(12, $multipleChoice['multiple_choice_count']);
        $this->assertSame(0, $multipleChoice['essay_count']);
        $this->assertSame('pilihan_ganda', $multipleChoice['question_type']);

        $this->assertSame(0, $essay['multiple_choice_count']);
        $this->assertSame(4, $essay['essay_count']);
        $this->assertSame('essay', $essay['question_type']);
    }
}
