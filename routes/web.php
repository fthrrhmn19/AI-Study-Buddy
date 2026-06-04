<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class, 'index'])->name('home');
Route::get('/docs', [WebController::class, 'docs'])->name('docs');
Route::get('/materials', [WebController::class, 'materials'])->name('materials.index');
Route::get('/ai/summarize', [WebController::class, 'summarize'])->name('ai.summarize');
Route::get('/ai/quiz', [WebController::class, 'quiz'])->name('ai.quiz');
Route::get('/ai/study-plan', [WebController::class, 'studyPlan'])->name('ai.study-plan');
Route::get('/ai/history', [WebController::class, 'history'])->middleware('auth')->name('ai.history');

Route::get('/documents/upload', [WebController::class, 'documentUpload'])->name('documents.upload');
Route::get('/documents/chat', [WebController::class, 'documentChat'])->name('documents.chat');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1')->name('register.store');
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->middleware('throttle:10,1')->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->middleware('throttle:10,1')->name('auth.google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
