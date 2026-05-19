@extends('layouts.app')

@section('title', 'Editar Documento')

@push('styles')
    @vite(['resources/css/modules/documentos.css'])
@endpush
@section('content')
    <div class="container-fluid px-0">
        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-pencil-square fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Edicao de Documento</h5>
            <p class="text-secondary mb-0">Sera implementado na proxima fase.</p>
        </div>
    </div>
@endsection
