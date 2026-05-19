@extends('layouts.admin')

@section('title', 'Organizacoes')

@push('styles')
    @vite(['resources/css/modules/admin/organizations.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/admin/organizations.js'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Organizacoes</h2>
                <p class="text-secondary mb-0 small">Escritorios ativos, trials e planos.</p>
            </div>
            <span class="badge text-bg-secondary rounded-pill px-3 py-2">
                {{ $organizations->total() }} escritorios
            </span>
        </div>

        <div class="surface-card p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(215,220,229,0.9);">
                            <th class="ps-4 py-3 text-secondary small text-uppercase fw-semibold" style="font-size:0.72rem;letter-spacing:.05em;">Escritorio</th>
                            <th class="py-3 text-secondary small text-uppercase fw-semibold" style="font-size:0.72rem;letter-spacing:.05em;">Plano</th>
                            <th class="py-3 text-secondary small text-uppercase fw-semibold" style="font-size:0.72rem;letter-spacing:.05em;">Status</th>
                            <th class="py-3 text-secondary small text-uppercase fw-semibold" style="font-size:0.72rem;letter-spacing:.05em;">Usuarios</th>
                            <th class="py-3 text-secondary small text-uppercase fw-semibold" style="font-size:0.72rem;letter-spacing:.05em;">Criado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($organizations as $org)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="stat-icon icon-blue flex-shrink-0" style="width:2.2rem;height:2.2rem;border-radius:0.65rem;font-size:0.85rem;">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold small">{{ $org->name }}</div>
                                            <div class="text-secondary" style="font-size:0.78rem;">{{ $org->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="badge text-bg-secondary rounded-pill">{{ strtoupper($org->plan) }}</span>
                                </td>
                                <td class="py-3">
                                    <span class="badge rounded-pill @if($org->status === 'active') text-bg-success @elseif($org->status === 'trial') text-bg-warning text-dark @else text-bg-secondary @endif">
                                        {{ ucfirst($org->status) }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="fw-semibold small">{{ $org->users_count ?? 0 }}</span>
                                </td>
                                <td class="py-3 text-secondary small">{{ $org->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">
                                    <i class="bi bi-building fs-2 d-block mb-2 opacity-50"></i>
                                    Nenhuma organizacao cadastrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($organizations->hasPages())
                <div class="px-4 py-3" style="border-top: 1px solid rgba(215,220,229,0.9);">
                    {{ $organizations->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
