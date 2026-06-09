<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiPrompt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AiPromptController extends Controller
{
    public function index(): View
    {
        $prompts = AiPrompt::orderByRaw("CASE WHEN key LIKE 'system.%' THEN 0 ELSE 1 END, key")
            ->with('updatedBy')
            ->get()
            ->groupBy(fn ($p) => str_starts_with($p->key, 'system.') ? 'system' : 'mock');

        return view('admin.prompts.index', compact('prompts'));
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $request->validate([
            'content' => ['required', 'string', 'min:10'],
        ]);

        $prompt = AiPrompt::where('key', $key)->firstOrFail();

        $prompt->update([
            'content'    => $request->input('content'),
            'updated_by' => auth()->id(),
        ]);

        Cache::forget("ai_prompt.{$key}");

        return response()->json([
            'success'    => true,
            'updated_at' => $prompt->updated_at->format('d/m/Y H:i'),
            'updated_by' => $prompt->updatedBy?->name ?? auth()->user()->name,
        ]);
    }

    public function reset(string $key): JsonResponse
    {
        $prompt  = AiPrompt::where('key', $key)->firstOrFail();
        $default = config("ai_prompts.{$key}", '');

        if (empty($default)) {
            return response()->json(['error' => 'Padrão não encontrado para esta chave.'], 422);
        }

        $prompt->update([
            'content'    => $default,
            'updated_by' => auth()->id(),
        ]);

        Cache::forget("ai_prompt.{$key}");

        return response()->json([
            'success'    => true,
            'content'    => $default,
            'updated_at' => $prompt->updated_at->format('d/m/Y H:i'),
        ]);
    }
}
