<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Traits\OrganizationScoped;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    use OrganizationScoped;

    public function index(Request $request): View
    {
        $orgId = $this->orgId();
        $userId = auth()->id();

        $query = SupportTicket::where('organization_id', $orgId)
            ->where('opened_by', $userId);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        if ($search = $request->get('search')) {
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
            );
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();

        $counts = SupportTicket::where('organization_id', $orgId)
            ->where('opened_by', $userId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('chamados.index', compact('tickets', 'counts'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category'    => 'required|in:tecnico,financeiro,duvida,sugestao,bug,outros',
            'priority'    => 'required|in:baixa,media,alta,critica',
        ]);

        $ticket = SupportTicket::create([
            ...$validated,
            'organization_id' => $this->orgId(),
            'opened_by'       => auth()->id(),
            'status'          => 'aberto',
        ]);

        return response()->json([
            'success'  => true,
            'protocol' => $ticket->protocol,
            'id'       => $ticket->id,
        ]);
    }
}
