<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$request = Illuminate\Http\Request::create('/api/ai/summarize', 'POST', [
    'title' => 'Test',
    'subject' => 'Test',
    'content' => 'Ini adalah materi test yang panjangnya harus lebih dari 40 karakter. Ini adalah materi test yang panjangnya harus lebih dari 40 karakter.',
    'save' => true
]);

$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
