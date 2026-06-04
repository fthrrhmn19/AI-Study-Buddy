<?php

use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\MaterialController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'app' => config('app.name'),
    'developer' => config('services.developer.name'),
    'database' => config('database.default'),
    'ai_provider' => 'Groq',
]));

Route::middleware('web')->group(function () {
    Route::apiResource('materials', MaterialController::class)->names('api.materials');

    Route::prefix('ai')->group(function () {
        Route::post('/summarize', [AiController::class, 'summarize']);
        Route::post('/quiz', [AiController::class, 'quiz']);
        Route::post('/study-plan', [AiController::class, 'studyPlan']);
        Route::post('/chat', [AiController::class, 'chatWithContent']);
        Route::get('/web-search', [AiController::class, 'webSearch']);

        Route::middleware('auth')->group(function () {
            Route::get('/history', [AiController::class, 'history']);
            Route::get('/history/{id}', [AiController::class, 'showHistory']);
            Route::post('/history/{id}/continue', [AiController::class, 'continueHistory']);
            Route::delete('/history/all', [AiController::class, 'deleteAllHistory']);
            Route::delete('/history/{id}', [AiController::class, 'deleteHistory']);
        });
    });

    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{id}', [DocumentController::class, 'show']);
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);

    Route::post('/documents/{id}/chat', [DocumentController::class, 'chat']);
    Route::post('/documents/{id}/summarize', [DocumentController::class, 'summarize']);
    Route::post('/documents/{id}/quiz', [DocumentController::class, 'generateQuiz']);
    Route::post('/documents/{id}/study-plan', [DocumentController::class, 'generateStudyPlan']);
    Route::get('/documents/{id}/history', [DocumentController::class, 'history'])->middleware('auth');
});
