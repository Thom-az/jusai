@extends('layouts.admin')

@section('title', 'Financeiro')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-semibold mb-1">Financeiro</h2>
                <p class="text-secondary mb-0 small">Assinaturas, faturas e receita recorrente.</p>
            </div>
        </div>

        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-currency-dollar fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Modulo Financeiro</h5>
            <p class="text-secondary mb-0">Listagem de invoices, MRR/ARR e status de assinaturas<br>serao implementados na proxima fase.</p>
        </div>
    </div>
@endsection
