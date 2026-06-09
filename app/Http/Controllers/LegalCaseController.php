<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLegalCaseRequest;
use App\Http\Requests\UpdateLegalCaseRequest;
use App\Models\LegalCase;
use App\Models\User;
use App\Traits\OrganizationScoped;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalCaseController extends Controller
{
    use OrganizationScoped;

    public function index(Request $request): View
    {
        $query = $this->scopedQuery(LegalCase::class)
            ->with(['creator', 'assignedUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }

        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->whereRaw('lower(title) like ?', ['%' . mb_strtolower($term) . '%'])
                  ->orWhereRaw('lower(client_name) like ?', ['%' . mb_strtolower($term) . '%']);
            });
        }

        $cases = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        $lawyers = User::where('organization_id', $this->orgId())
            ->whereIn('role', ['org_admin', 'lawyer'])
            ->orderBy('name')
            ->get();

        return view('casos.index', compact('cases', 'lawyers'));
    }

    public function create(): View
    {
        $lawyers = User::where('organization_id', $this->orgId())
            ->whereIn('role', ['org_admin', 'lawyer'])
            ->orderBy('name')
            ->get();

        return view('casos.create', compact('lawyers'));
    }

    public function store(StoreLegalCaseRequest $request): RedirectResponse
    {
        $case = LegalCase::create([
            ...$request->validated(),
            'organization_id' => $this->orgId(),
            'created_by'      => auth()->id(),
            'status'          => $request->input('status', 'triagem'),
        ]);

        $this->logActivity('caso_criado', "Caso \"{$case->title}\" criado.", LegalCase::class, $case->id);

        return redirect()->route('cases.show', $case)->with('success', 'Caso criado com sucesso.');
    }

    public function show(string $id): View
    {
        $case = $this->scopedQuery(LegalCase::class)
            ->with(['documents', 'aiReviews.creator', 'assignedUser', 'creator'])
            ->findOrFail($id);

        $lawyers = User::where('organization_id', $this->orgId())
            ->whereIn('role', ['org_admin', 'lawyer'])
            ->orderBy('name')
            ->get();

        $cases = $this->scopedQuery(LegalCase::class)
            ->whereNotIn('status', ['encerrado', 'arquivado'])
            ->orderBy('title')
            ->get();

        return view('casos.show', compact('case', 'lawyers', 'cases'));
    }

    public function edit(string $id): View
    {
        $case = $this->scopedQuery(LegalCase::class)->findOrFail($id);

        $lawyers = User::where('organization_id', $this->orgId())
            ->whereIn('role', ['org_admin', 'lawyer'])
            ->orderBy('name')
            ->get();

        return view('casos.edit', compact('case', 'lawyers'));
    }

    public function update(UpdateLegalCaseRequest $request, string $id): RedirectResponse
    {
        $case = $this->scopedQuery(LegalCase::class)->findOrFail($id);
        $case->update($request->validated());

        $this->logActivity('caso_atualizado', "Caso \"{$case->title}\" atualizado.", LegalCase::class, $case->id);

        return redirect()->route('cases.show', $case)->with('success', 'Caso atualizado.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $case = $this->scopedQuery(LegalCase::class)->findOrFail($id);
        $title = $case->title;
        $case->delete();

        $this->logActivity('caso_excluido', "Caso \"{$title}\" excluído.", LegalCase::class, $id);

        return redirect()->route('cases.index')->with('success', 'Caso excluído.');
    }
}
