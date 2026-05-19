<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $user = Auth::user();

            if ($user) {
                $shellUser = [
                    'name'     => $user->name,
                    'role'     => $this->roleLabel($user->role),
                    'initials' => $this->initials($user->name),
                ];
            } else {
                $shellUser = config('jusai.shell.user');
            }

            $view->with('shellUser', $shellUser);
            $view->with('shellNavigation', config('jusai.shell.navigation'));
            $view->with('adminNavigation', config('jusai.shell.admin_navigation'));
        });
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            'super_admin' => 'Admin JusAI',
            'org_admin'   => 'Administrador do escritorio',
            'lawyer'      => 'Advogado',
            'assistant'   => 'Assistente',
            default       => $role,
        };
    }

    private function initials(string $name): string
    {
        $parts = explode(' ', trim($name));
        $first = mb_substr($parts[0], 0, 1);
        $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

        return mb_strtoupper($first . $last);
    }
}
