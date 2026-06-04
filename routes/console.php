<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about-project', function () {
    $this->info('AI Study Buddy - Laravel + MongoDB + Groq API');
    $this->line('Fitur: summarizer, quiz generator, study plan, history AI.');
})->purpose('Show project summary');
