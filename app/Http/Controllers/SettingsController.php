<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SettingsController extends Controller
{
    public function perfil(): View
    {
        return view('configuracoes.perfil', ['current' => 'perfil']);
    }

    public function escritorio(): View
    {
        return view('configuracoes.escritorio', ['current' => 'escritorio']);
    }

    public function equipe(): View
    {
        return view('configuracoes.equipe', ['current' => 'equipe']);
    }

    public function preferencias(): View
    {
        return view('configuracoes.preferencias', ['current' => 'preferencias']);
    }

    public function seguranca(): View
    {
        return view('configuracoes.seguranca', ['current' => 'seguranca']);
    }

    public function plano(): View
    {
        return view('configuracoes.plano', ['current' => 'plano']);
    }
}
