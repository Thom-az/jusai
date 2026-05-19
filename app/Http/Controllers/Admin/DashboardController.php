<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\SupportTicket;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $mrrCents = Subscription::where('status', 'active')
            ->where('billing_cycle', 'monthly')
            ->sum('price_cents');

        $annualMrrCents = (int) (Subscription::where('status', 'active')
            ->where('billing_cycle', 'annual')
            ->sum('price_cents') / 12);

        $totalMrrCents = $mrrCents + $annualMrrCents;

        $metrics = [
            [
                'label'      => 'MRR',
                'value'      => 'R$ ' . number_format($totalMrrCents / 100, 2, ',', '.'),
                'trend'      => Organization::where('status', 'active')->count() . ' escritorios ativos',
                'icon'       => 'bi-currency-dollar',
                'icon_class' => 'icon-green',
            ],
            [
                'label'      => 'Organizacoes',
                'value'      => Organization::count(),
                'trend'      => Organization::where('status', 'trial')->count() . ' em trial',
                'icon'       => 'bi-building',
                'icon_class' => 'icon-blue',
            ],
            [
                'label'      => 'Chamados abertos',
                'value'      => SupportTicket::whereIn('status', ['aberto', 'em_andamento'])->count(),
                'trend'      => SupportTicket::where('priority', 'critica')->whereIn('status', ['aberto', 'em_andamento'])->count() . ' criticos',
                'icon'       => 'bi-headset',
                'icon_class' => 'icon-red',
            ],
            [
                'label'      => 'Leads no funil',
                'value'      => Lead::whereNotIn('status', ['ganho', 'perdido', 'inativo'])->count(),
                'trend'      => Lead::where('status', 'demo_agendada')->count() . ' demos agendadas',
                'icon'       => 'bi-person-lines-fill',
                'icon_class' => 'icon-gold',
            ],
        ];

        $recentOrganizations = Organization::with('subscriptions')
            ->latest()
            ->take(5)
            ->get();

        $openTickets = SupportTicket::with('organization', 'opener')
            ->whereIn('status', ['aberto', 'em_andamento'])
            ->orderByRaw("CASE priority WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('metrics', 'recentOrganizations', 'openTickets'));
    }
}
