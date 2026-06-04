<?php

namespace App\Http\Controllers;

use App\Models\AiGeneration;
use App\Models\DocumentChat;
use App\Models\Material;
use App\Models\Document;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Throwable;

class WebController extends Controller
{
    public function index(): View
    {
        return $this->homeView();
    }

    private function homeView(?string $activeFeature = null): View
    {
        $materialsCount = 0;
        $aiCount = 0;
        $recentMaterials = collect();
        $dbWarning = null;

        try {
            if (Auth::check()) {
                $userId = (string) Auth::id();
                $materialsCount = Material::query()->where('user_id', $userId)->count();
                $documentCount = Document::query()->where('user_id', $userId)->count();
                $materialsCount += $documentCount;
                $legacyTable = (string) config('services.ai_history.legacy_collection', 'ai_histories');
                $legacyAiCount = (new AiGeneration())->setTable($legacyTable)->newQuery()->where('user_id', $userId)->count();
                $documentAiCount = DocumentChat::query()
                    ->where('user_id', $userId)
                    ->whereIn('action_type', ['chat', 'summarize', 'summary', 'quiz', 'study_plan'])
                    ->count();
                $aiCount = AiGeneration::query()->where('user_id', $userId)->count() + $legacyAiCount + $documentAiCount;
                $recentMaterials = Material::query()->where('user_id', $userId)->latest()->limit(5)->get();
            }
        } catch (Throwable $exception) {
            $dbWarning = 'MongoDB belum terhubung. Isi MONGODB_URI dan MONGODB_DATABASE di .env lokal atau environment VPS.';
        }

        return view('home', [
            'materialsCount' => $materialsCount,
            'aiCount' => $aiCount,
            'recentMaterials' => $recentMaterials,
            'dbWarning' => $dbWarning,
            'activeFeature' => $activeFeature,
        ]);
    }

    public function docs(): View
    {
        return view('docs');
    }

    public function materials(): View
    {
        return view('materials.index');
    }

    public function summarize(): View
    {
        return view('ai.summarize');
    }

    public function quiz(): View
    {
        return view('ai.quiz');
    }

    public function studyPlan(): View
    {
        return view('ai.study-plan');
    }

    public function history(): View
    {
        return view('ai.history');
    }

    public function documentUpload(): View
    {
        return view('documents.upload');
    }

    public function documentChat(): View
    {
        return view('documents.chat');
    }
}
