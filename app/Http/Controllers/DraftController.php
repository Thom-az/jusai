<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMinutaDraft;
use App\Models\Draft;
use App\Models\LegalCase;
use App\Traits\OrganizationScoped;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DraftController extends Controller
{
    use OrganizationScoped;

    public function index(): View
    {
        $drafts = $this->scopedQuery(Draft::class)
            ->with(['legalCase', 'creator'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('minutas.index', compact('drafts'));
    }

    public function create(): View
    {
        $cases = $this->scopedQuery(LegalCase::class)
            ->whereNotIn('status', ['encerrado', 'arquivado'])
            ->orderBy('title')
            ->get();

        return view('minutas.create', compact('cases'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'type'          => ['required', 'in:notificacao_extrajudicial,contrato,peticao_inicial,contestacao,recurso,parecer,outros'],
            'legal_case_id' => ['nullable', 'uuid'],
            'instructions'  => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        if ($data['legal_case_id']) {
            $this->scopedQuery(LegalCase::class)->findOrFail($data['legal_case_id']);
        }

        $draft = Draft::create([
            'organization_id' => $this->orgId(),
            'legal_case_id'   => $data['legal_case_id'] ?? null,
            'title'           => $data['title'],
            'type'            => $data['type'],
            'instructions'    => $data['instructions'],
            'content'         => '',
            'status'          => 'rascunho',
            'generated_by_ai' => true,
            'created_by'      => auth()->id(),
        ]);

        ProcessMinutaDraft::dispatch($draft);

        $this->logActivity('minuta_criada', "Minuta \"{$draft->title}\" criada para geração por IA.", Draft::class, $draft->id);

        return redirect()->route('drafts.show', $draft)->with('info', 'Minuta em geração. Aguarde o processamento.');
    }

    public function show(string $id): View
    {
        $draft = $this->scopedQuery(Draft::class)
            ->with(['legalCase', 'creator', 'reviewer'])
            ->findOrFail($id);

        $isGenerating = $draft->generated_by_ai && $draft->content === '';

        return view('minutas.show', compact('draft', 'isGenerating'));
    }

    public function edit(string $id): View
    {
        $draft = $this->scopedQuery(Draft::class)->with('legalCase')->findOrFail($id);

        $cases = $this->scopedQuery(LegalCase::class)
            ->whereNotIn('status', ['encerrado', 'arquivado'])
            ->orderBy('title')
            ->get();

        return view('minutas.edit', compact('draft', 'cases'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $draft = $this->scopedQuery(Draft::class)->findOrFail($id);

        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'content'       => ['required', 'string'],
            'status'        => ['required', 'in:rascunho,em_revisao,aprovado,rejeitado,publicado'],
            'legal_case_id' => ['nullable', 'uuid'],
        ]);

        if ($data['legal_case_id']) {
            $this->scopedQuery(LegalCase::class)->findOrFail($data['legal_case_id']);
        }

        $draft->update($data);

        $this->logActivity('minuta_atualizada', "Minuta \"{$draft->title}\" atualizada.", Draft::class, $draft->id);

        return redirect()->route('drafts.show', $draft)->with('success', 'Minuta salva com sucesso.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $draft = $this->scopedQuery(Draft::class)->findOrFail($id);
        $title = $draft->title;

        $draft->delete();

        $this->logActivity('minuta_excluida', "Minuta \"{$title}\" excluída.", Draft::class, $id);

        return redirect()->route('drafts.index')->with('success', 'Minuta excluída.');
    }

    public function status(string $id): JsonResponse
    {
        $draft = $this->scopedQuery(Draft::class)->findOrFail($id);

        $ready = ! ($draft->generated_by_ai && $draft->content === '');

        return response()->json([
            'ready'   => $ready,
            'content' => $ready ? mb_substr($draft->content, 0, 100) : null,
        ]);
    }
}
