@extends('layouts.app')

@section('title', 'Minutas')

@push('styles')
    @vite(['resources/css/modules/minutas.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Minutas</h2>
                <p class="text-secondary mb-0 small">Rascunhos juridicos gerados e revisados.</p>
            </div>
            <a href="{{ route('drafts.create') }}" wire:navigate class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-magic me-2"></i>Nova minuta
            </a>
        </div>

        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-journal-richtext fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Gerador de Minutas com IA</h5>
            <p class="text-secondary mb-0">Editor de rascunhos, versionamento e fluxo de revisao<br>serao implementados na proxima fase.</p>
        </div>
    </div>
@endsection
