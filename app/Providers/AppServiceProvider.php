<?php

namespace App\Providers;

use App\Services\AnthropicService;
use App\Services\EmbeddingService;
use App\Services\SupabaseStorageService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SupabaseStorageService::class, fn () => new SupabaseStorageService(
            supabaseUrl:    config('services.supabase.url'),
            serviceRoleKey: config('services.supabase.service_role_key'),
        ));

        $this->app->singleton(EmbeddingService::class, fn () => new EmbeddingService(
            apiKey: config('services.openai.key', ''),
        ));

        $this->app->singleton(AnthropicService::class, fn () => new AnthropicService(
            apiKey:      config('services.anthropic.key', ''),
            temperature: (float) config('jusai.ai.temperature'),
            modelFast:   config('jusai.ai.model_fast'),
            modelStrong: config('jusai.ai.model_strong'),
            provider:    config('jusai.ai.provider', 'mock'),
        ));
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        RateLimiter::for('ai', function (Request $request) {
            return Limit::perHour(30)->by($request->user()?->id ?: $request->ip());
        });

        View::composer([
            'layouts.app',
            'layouts.partials.navbar',
            'layouts.partials.sidebar',
        ], function ($view) {
            $view->with('shellUser', $this->shellUser());
            $view->with('shellNavigation', config('jusai.shell.navigation'));
        });

        View::composer([
            'layouts.admin',
            'layouts.partials.admin-navbar',
            'layouts.partials.admin-sidebar',
        ], function ($view) {
            $view->with('shellUser', $this->shellUser());
            $view->with('adminNavigation', config('jusai.shell.admin_navigation'));
        });
    }

    private function shellUser(): array
    {
        $user = Auth::user();

        if (! $user) {
            return config('jusai.shell.user');
        }

        return [
            'name'     => $user->name,
            'role'     => $this->roleLabel($user->role),
            'initials' => $this->initials($user->name),
        ];
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
