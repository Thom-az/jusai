<?php

namespace App\Http\Controllers;

use App\Http\Requests\TriggerAiReviewRequest;
use App\Jobs\ProcessAiReview;
use App\Models\AiReview;
use App\Models\Document;
use App\Models\Draft;
use App\Models\LegalCase;
use App\Traits\OrganizationScoped;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AiReviewController extends Controller
{
    use OrganizationScoped;

    public function index(): View
    {
        $cases = $this->scopedQuery(LegalCase::class)
            ->whereNotIn('status', ['encerrado', 'arquivado'])
            ->orderBy('title')
            ->get();

        $documents = $this->scopedQuery(Document::class)
            ->where('status', 'ready')
            ->orderBy('title')
            ->get();

        $drafts = $this->scopedQuery(Draft::class)
            ->orderBy('title')
            ->get();

        $reviews = $this->scopedQuery(AiReview::class)
            ->with(['legalCase', 'document', 'creator'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('revisor.index', compact('cases', 'documents', 'drafts', 'reviews'));
    }

    public function store(TriggerAiReviewRequest $request): RedirectResponse
    {
        $caseId = $request->input('legal_case_id');
        $this->scopedQuery(LegalCase::class)->findOrFail($caseId);

        $promptUsed = $request->type === 'pesquisa_juridica'
            ? $request->input('question')
            : null;

        $review = AiReview::create([
            'organization_id'       => $this->orgId(),
            'legal_case_id'         => $caseId,
            'document_id'           => $request->input('document_id'),
            'draft_id'              => $request->input('draft_id'),
            'type'                  => $request->input('type'),
            'prompt_used'           => $promptUsed,
            'result'                => '',
            'status'                => 'processando',
            'requires_human_review' => true,
            'created_by'            => auth()->id(),
        ]);

        ProcessAiReview::dispatch($review);

        $this->logActivity('analise_iniciada', "Análise de IA ({$review->type}) iniciada.", AiReview::class, $review->id);

        return redirect()->route('review.show', $review)->with('info', 'Análise iniciada. Aguarde o processamento.');
    }

    public function show(string $id): View
    {
        $review = $this->scopedQuery(AiReview::class)
            ->with(['legalCase', 'document', 'draft', 'creator', 'reviewer'])
            ->findOrFail($id);

        $isProcessing = $review->status === 'processando';

        return view('revisor.show', compact('review', 'isProcessing'));
    }

    public function status(string $id): JsonResponse
    {
        $review = $this->scopedQuery(AiReview::class)->findOrFail($id);

        return response()->json([
            'status' => $review->status,
            'ready'  => $review->status === 'concluido',
        ]);
    }

    public function approve(string $id): RedirectResponse
    {
        $review = $this->scopedQuery(AiReview::class)->findOrFail($id);

        $review->update([
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->logActivity('analise_aprovada', "Análise de IA revisada e aprovada.", AiReview::class, $review->id);

        return redirect()->route('review.show', $review)->with('success', 'Revisão confirmada.');
    }

    public function feedback(\Illuminate\Http\Request $request, string $id): RedirectResponse
    {
        $review = $this->scopedQuery(AiReview::class)->findOrFail($id);

        $data = $request->validate([
            'feedback_rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'feedback_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review->update($data);

        return redirect()->route('review.show', $review)->with('success', 'Feedback enviado. Obrigado!');
    }
}
