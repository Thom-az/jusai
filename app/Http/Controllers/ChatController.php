<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\LegalCase;
use App\Traits\OrganizationScoped;
use Illuminate\View\View;

class ChatController extends Controller
{
    use OrganizationScoped;

    public function index(): View
    {
        $conversations = AiConversation::where('user_id', auth()->id())
            ->whereHas('legalCase', fn ($q) => $q->where('organization_id', $this->orgId()))
            ->with(['legalCase', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest()
            ->get();

        $cases = $this->scopedQuery(LegalCase::class)
            ->whereNotIn('status', ['encerrado', 'arquivado'])
            ->orderBy('title')
            ->get();

        return view('chat.index', compact('conversations', 'cases'));
    }

    public function show(string $caso): View
    {
        $caso = $this->scopedQuery(LegalCase::class)->findOrFail($caso);

        return view('casos.chat', compact('caso'));
    }
}
