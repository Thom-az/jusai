@extends('layouts.admin')

@section('title', 'Organizacoes')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-semibold mb-1">Organizacoes</h2>
                <p class="text-secondary mb-0 small">Escritorios ativos, trials e planos.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Escritorio</th>
                                <th>Plano</th>
                                <th>Status</th>
                                <th>Usuarios</th>
                                <th>Criado em</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($organizations as $org)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-medium">{{ $org->name }}</div>
                                        <div class="small text-secondary">{{ $org->email }}</div>
                                    </td>
                                    <td><span class="badge text-bg-light border rounded-pill">{{ $org->plan }}</span></td>
                                    <td>
                                        <span class="badge @if($org->status === 'active') text-bg-success @elseif($org->status === 'trial') text-bg-warning @else text-bg-secondary @endif rounded-pill">
                                            {{ $org->status }}
                                        </span>
                                    </td>
                                    <td>{{ $org->users_count ?? 0 }}</td>
                                    <td class="text-secondary small">{{ $org->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-5">Nenhuma organizacao cadastrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
