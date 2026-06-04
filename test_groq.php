<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $apiKey = config('services.groq.key');
    $baseUrl = rtrim(config('services.groq.base_url'), '/');
    $model = config('services.groq.model');
    
    echo "API Key configured: " . (empty($apiKey) ? 'NO' : 'YES') . PHP_EOL;
    echo "Base URL: " . $baseUrl . PHP_EOL;
    echo "Model: " . $model . PHP_EOL;
    
    $response = Illuminate\Support\Facades\Http::timeout(15)
        ->acceptJson()
        ->withToken($apiKey)
        ->post($baseUrl . '/chat/completions', [
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => 'Say hi']],
            'max_completion_tokens' => 30,
        ]);
    
    echo "Status: " . $response->status() . PHP_EOL;
    echo "Body: " . substr($response->body(), 0, 800) . PHP_EOL;
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
