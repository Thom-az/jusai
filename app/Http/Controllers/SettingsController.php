<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SettingsController extends Controller
{
    public function perfil(): View
    {
        return view('configuracoes.perfil');
    }

    public function escritorio(): View
    {
        return view('configuracoes.escritorio');
    }

    public function equipe(): View
    {
        return view('configuracoes.equipe');
    }

    public function preferencias(): View
    {
        return view('configuracoes.preferencias');
    }

    public function seguranca(): View
    {
        return view('configuracoes.seguranca');
    }

    public function plano(): View
    {
        return view('configuracoes.plano');
    }
}
