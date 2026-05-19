@extends('layouts.app')

@section('title', 'Documentos')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Documentos</h2>
                <p class="text-secondary mb-0 small">PDFs, DOCX e anexos analisados por IA.</p>
            </div>
            <a href="{{ route('documents.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-cloud-arrow-up me-2"></i>Enviar documento
            </a>
        </div>

        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-file-earmark-text fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Biblioteca de Documentos</h5>
            <p class="text-secondary mb-0">Upload para Supabase Storage, OCR e analise por IA<br>serao implementados na proxima fase.</p>
        </div>
    </div>
@endsection
