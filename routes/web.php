<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::view('/casos', 'placeholders.module', [
    'moduleTitle' => 'Gestão de Casos',
    'moduleEyebrow' => 'Módulo em preparação',
    'moduleDescription' => 'A listagem com DataTables, filtros avançados e fluxo completo de cadastro será a próxima entrega do MVP.',
    'moduleIcon' => 'bi-briefcase',
    'moduleAction' => 'Criar listagem, filtros e formulário de caso.',
])->name('cases.index');

Route::view('/casos/novo', 'placeholders.module', [
    'moduleTitle' => 'Novo Caso',
    'moduleEyebrow' => 'Cadastro guiado',
    'moduleDescription' => 'Vamos estruturar o formulário de criação de casos com cliente, área jurídica, risco e resumo inicial.',
    'moduleIcon' => 'bi-folder-plus',
    'moduleAction' => 'Criar request, formulário e persistência.',
])->name('cases.create');

Route::view('/documentos', 'placeholders.module', [
    'moduleTitle' => 'Documentos',
    'moduleEyebrow' => 'Upload e processamento',
    'moduleDescription' => 'Aqui entraremos com Dropzone.js, visualizador de PDF e serviços mockados de extração e resumo.',
    'moduleIcon' => 'bi-file-earmark-pdf',
    'moduleAction' => 'Integrar upload, validações e visualização.',
])->name('documents.index');

Route::view('/minutas', 'placeholders.module', [
    'moduleTitle' => 'Minutas Jurídicas',
    'moduleEyebrow' => 'Editor e versionamento',
    'moduleDescription' => 'Esta área receberá o editor rico, geração assistida e controle de versões dos rascunhos jurídicos.',
    'moduleIcon' => 'bi-journal-richtext',
    'moduleAction' => 'Criar fluxo de geração e edição de minutas.',
])->name('drafts.index');

Route::view('/revisor', 'placeholders.module', [
    'moduleTitle' => 'Revisor Jurídico',
    'moduleEyebrow' => 'Validação assistida',
    'moduleDescription' => 'O revisor mockado destacará problemas, lacunas e recomendações por severidade antes da integração com IA real.',
    'moduleIcon' => 'bi-shield-check',
    'moduleAction' => 'Implementar checklist e retorno mockado.',
])->name('review.index');

Route::view('/configuracoes', 'placeholders.module', [
    'moduleTitle' => 'Configurações',
    'moduleEyebrow' => 'Organização e IA',
    'moduleDescription' => 'Nesta etapa futura vamos concentrar organização, usuários, modelos e preferências operacionais da IA.',
    'moduleIcon' => 'bi-sliders',
    'moduleAction' => 'Criar telas de escritório, usuários e preferências.',
])->name('settings.index');
