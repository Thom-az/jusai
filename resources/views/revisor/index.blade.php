@extends('layouts.app')

@section('title', 'Revisor Juridico')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Revisor Juridico</h2>
                <p class="text-secondary mb-0 small">Analise e revisao de pecas com suporte de IA.</p>
            </div>
        </div>

        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-shield-check fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Revisor com IA</h5>
            <p class="text-secondary mb-0">Analise de documentos, revisao de minutas e pesquisa juridica<br>serao conectados ao provider de IA na proxima fase.</p>
        </div>
    </div>
@endsection
