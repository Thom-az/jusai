<?php

namespace App\Livewire\Admin\Configuracoes;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Preferencias extends Component
{
    // ─── Aparência ─────────────────────────────────────────────────────────────
    public string $theme = 'system';  // light | dark | system

    // ─── Regional ──────────────────────────────────────────────────────────────
    public string $timezone = 'America/Sao_Paulo';

    // ─── Notificações — canais ─────────────────────────────────────────────────
    public bool $notifyEmail   = true;
    public bool $notifyBrowser = false;

    // ─── Notificações — horário de silêncio ────────────────────────────────────
    public bool   $quietEnabled = false;
    public string $quietStart   = '22:00';
    public string $quietEnd     = '08:00';

    // ─── Notificações — matriz de eventos ──────────────────────────────────────
    /** @var array<string, array{email: bool, browser: bool}> */
    public array $notifEvents = [];

    // ─── Feedback de salvo ─────────────────────────────────────────────────────
    public bool $saved = false;

    // ─── Fusos horários disponíveis ────────────────────────────────────────────
    public array $timezones = [
        'Brasil' => [
            'America/Sao_Paulo'    => 'São Paulo / Rio / Brasília (UTC-3)',
            'America/Bahia'        => 'Salvador / Bahia (UTC-3)',
            'America/Fortaleza'    => 'Fortaleza / Recife / Maceió (UTC-3)',
            'America/Belem'        => 'Belém / Macapá (UTC-3)',
            'America/Manaus'       => 'Manaus (UTC-4)',
            'America/Cuiaba'       => 'Cuiabá (UTC-4)',
            'America/Campo_Grande' => 'Campo Grande (UTC-4)',
            'America/Porto_Velho'  => 'Porto Velho (UTC-4)',
            'America/Rio_Branco'   => 'Rio Branco (UTC-5)',
            'America/Noronha'      => 'Fernando de Noronha (UTC-2)',
        ],
        'Internacional' => [
            'UTC'              => 'UTC — Tempo Universal',
            'America/New_York' => 'Nova York (UTC-5/-4)',
            'Europe/Lisbon'    => 'Lisboa (UTC+0/+1)',
            'Europe/London'    => 'Londres (UTC+0/+1)',
            'Europe/Madrid'    => 'Madri / Paris (UTC+1/+2)',
        ],
    ];

    // ─── Labels dos eventos ────────────────────────────────────────────────────
    public array $eventLabels = [
        'case_assigned'     => ['Caso atribuído a mim',       'Quando um caso for diretamente atribuído a você'],
        'case_updated'      => ['Atualização em caso',        'Movimentações em casos que você participa'],
        'document_uploaded' => ['Documento enviado',          'Novo documento adicionado a um caso seu'],
        'ai_completed'      => ['Análise de IA concluída',    'Resultado de análise de IA disponível'],
        'team_invited'      => ['Novo membro na equipe',      'Quando um membro for adicionado ao escritório'],
        'billing_invoice'   => ['Fatura disponível',          'Nova fatura gerada para o escritório'],
    ];

    // ─── Defaults de eventos ───────────────────────────────────────────────────
    private array $defaultEvents = [
        'case_assigned'     => ['email' => true,  'browser' => true],
        'case_updated'      => ['email' => true,  'browser' => false],
        'document_uploaded' => ['email' => false, 'browser' => true],
        'ai_completed'      => ['email' => true,  'browser' => true],
        'team_invited'      => ['email' => true,  'browser' => false],
        'billing_invoice'   => ['email' => true,  'browser' => false],
    ];

    // ─── Inicialização ──────────────────────────────────────────────────────────

    public function mount(): void
    {
        $user = Auth::user();

        $this->theme    = $user->theme    ?? 'system';
        $this->timezone = $user->timezone ?? 'America/Sao_Paulo';

        $prefs = $user->notification_prefs ?? [];

        $this->notifyEmail   = $prefs['channels']['email']   ?? true;
        $this->notifyBrowser = $prefs['channels']['browser'] ?? false;
        $this->quietEnabled  = $prefs['quiet']['enabled']    ?? false;
        $this->quietStart    = $prefs['quiet']['start']      ?? '22:00';
        $this->quietEnd      = $prefs['quiet']['end']        ?? '08:00';

        // Merge saved events sobre os defaults para garantir todas as chaves
        $saved = $prefs['events'] ?? [];
        $this->notifEvents = array_merge($this->defaultEvents, $saved);
    }

    // ─── Tema — aplica imediatamente, persiste no banco ───────────────────────

    public function setTheme(string $theme): void
    {
        if (! in_array($theme, ['light', 'dark', 'system'], true)) {
            return;
        }

        $this->theme = $theme;

        $user = Auth::user();
        $user->theme = $theme;
        $user->save();

        // Resolve o tema real para o JS
        $resolved = $theme === 'system' ? null : $theme;

        $this->dispatch('apply-theme-preference', theme: $theme, resolved: $resolved);
    }

    // ─── Salvar preferências regionais e de notificação ───────────────────────

    public function salvar(): void
    {
        $this->validate([
            'timezone'    => ['required', 'timezone'],
            'quietStart'  => ['required_if:quietEnabled,true', 'nullable', 'date_format:H:i'],
            'quietEnd'    => ['required_if:quietEnabled,true', 'nullable', 'date_format:H:i'],
            'notifEvents' => ['nullable', 'array'],
        ], [
            'timezone.required'        => 'Selecione um fuso horário.',
            'timezone.timezone'        => 'Fuso horário inválido.',
            'quietStart.date_format'   => 'Formato de hora inválido (use HH:MM).',
            'quietEnd.date_format'     => 'Formato de hora inválido (use HH:MM).',
            'quietStart.required_if'   => 'Informe o início do horário de silêncio.',
            'quietEnd.required_if'     => 'Informe o fim do horário de silêncio.',
        ]);

        $prefs = [
            'channels' => [
                'email'   => $this->notifyEmail,
                'browser' => $this->notifyBrowser,
            ],
            'quiet' => [
                'enabled' => $this->quietEnabled,
                'start'   => $this->quietStart,
                'end'     => $this->quietEnd,
            ],
            'events' => $this->notifEvents,
        ];

        $user = Auth::user();

        $oldTimezone = $user->timezone;

        $user->timezone           = $this->timezone;
        $user->notification_prefs = $prefs;
        $user->save();

        if ($oldTimezone !== $this->timezone) {
            ActivityLog::create([
                'organization_id' => $user->organization_id,
                'user_id'         => $user->id,
                'event'           => 'updated',
                'description'     => 'Fuso horário alterado: ' . $oldTimezone . ' → ' . $this->timezone,
                'subject_type'    => \App\Models\User::class,
                'subject_id'      => (string) $user->id,
                'ip_address'      => request()->ip(),
            ]);
        }

        $this->saved = true;
        $this->dispatch('preferencias-saved');
    }

    // ─── Render ─────────────────────────────────────────────────────────────────

    public function placeholder(): string
    {
        return <<<'HTML'
        <div>
            <div class="settings-skeleton-card placeholder-glow mb-3">
                <div class="placeholder col-5 rounded-3 mb-3" style="height:1.1rem"></div>
                <div class="placeholder col-12 rounded-3 mb-2" style="height:.9rem"></div>
                <div class="placeholder col-8 rounded-3 mb-2" style="height:.9rem"></div>
                <div class="placeholder col-6 rounded-3" style="height:.9rem"></div>
            </div>
            <div class="settings-skeleton-card placeholder-glow">
                <div class="placeholder col-4 rounded-3 mb-3" style="height:1.1rem"></div>
                <div class="placeholder col-12 rounded-3 mb-2" style="height:.9rem"></div>
                <div class="placeholder col-7 rounded-3" style="height:.9rem"></div>
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.admin.configuracoes.preferencias');
    }
}
