<?php

namespace App\Livewire\Admin\Configuracoes;

use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQRCode;

class Seguranca extends Component
{
    // ─── 2FA ───────────────────────────────────────────────────────────────────
    /** idle | setup | show-codes | disable */
    public string $twoFaStep    = 'idle';
    public bool   $twoFaEnabled = false;
    public string $twoFaCode    = '';       // input do usuário para verificação

    /** Exibidos uma vez após ativar 2FA */
    public array $newRecoveryCodes = [];

    // ─── Sessões ────────────────────────────────────────────────────────────────
    public string $sessionPassword = '';

    // ─── Política de senha (admin-only) ────────────────────────────────────────
    public bool $canManagePolicy = false;
    public bool $policyEnabled   = false;
    public int  $policyMinLength = 8;
    public bool $policyUppercase = true;
    public bool $policyNumbers   = true;
    public bool $policySymbols   = false;
    public int  $policyExpiry    = 0;       // 0 = nunca

    /** Feedback de salvo para política */
    public bool $policySaved = false;

    // ─── LGPD ──────────────────────────────────────────────────────────────────
    public bool   $exportRequested = false;
    public bool   $deleteConfirm   = false;
    public string $deletePassword  = '';

    // ─── Inicialização ──────────────────────────────────────────────────────────

    public function mount(): void
    {
        $user = Auth::user();

        // 2FA
        if ($user->two_factor_confirmed_at !== null) {
            $this->twoFaEnabled = true;
            $this->twoFaStep    = 'idle';
        } elseif ($user->two_factor_secret !== null) {
            // Configuração iniciada mas não concluída — retoma
            $this->twoFaEnabled = false;
            $this->twoFaStep    = 'setup';
        } else {
            $this->twoFaEnabled = false;
            $this->twoFaStep    = 'idle';
        }

        // Política de senha
        $this->canManagePolicy = $user->can('manage-security-policy');

        if ($org = $user->organization) {
            $policy = $org->password_policy ?? [];

            $this->policyEnabled  = (bool) ($policy['enabled']     ?? false);
            $this->policyMinLength = (int)  ($policy['min_length']  ?? 8);
            $this->policyUppercase = (bool) ($policy['uppercase']   ?? true);
            $this->policyNumbers   = (bool) ($policy['numbers']     ?? true);
            $this->policySymbols   = (bool) ($policy['symbols']     ?? false);
            $this->policyExpiry    = (int)  ($policy['expiry_days'] ?? 0);
        }
    }

    // =========================================================================
    // Computed: chave manual e QR SVG (cache por request)
    // =========================================================================

    #[Computed]
    public function manualKey(): string
    {
        $secret = Auth::user()->two_factor_secret;

        if (! $secret) {
            return '';
        }

        // Formata em grupos de 4 para facilitar digitação manual
        return implode(' ', str_split(strtoupper($secret), 4));
    }

    #[Computed]
    public function qrCodeSvg(): string
    {
        $user   = Auth::user();
        $secret = $user->two_factor_secret;

        if (! $secret) {
            return '';
        }

        try {
            $google2fa = new Google2FAQRCode();

            return $google2fa->getQRCodeInline(
                config('app.name', 'JusAI'),
                $user->email,
                $secret,
                200
            );
        } catch (\Throwable $e) {
            return '';
        }
    }

    // =========================================================================
    // 2FA — Ativar
    // =========================================================================

    public function initTwoFa(): void
    {
        $user = Auth::user();

        if ($user->two_factor_confirmed_at !== null) {
            return; // já ativado
        }

        // Gera secret (só se ainda não tiver um pendente)
        if (! $user->two_factor_secret) {
            $google2fa = new Google2FAQRCode();

            $user->two_factor_secret         = $google2fa->generateSecretKey();
            $user->two_factor_recovery_codes = null;
            $user->two_factor_confirmed_at   = null;
            $user->save();
        }

        unset($this->qrCodeSvg, $this->manualKey);

        $this->twoFaCode = '';
        $this->twoFaStep = 'setup';
    }

    public function confirmTwoFa(): void
    {
        $this->validate(
            ['twoFaCode' => ['required', 'digits:6']],
            ['twoFaCode.required' => 'Digite o código gerado pelo aplicativo.',
             'twoFaCode.digits'   => 'O código deve ter exatamente 6 dígitos.']
        );

        $user   = Auth::user();
        $secret = $user->two_factor_secret;

        if (! $secret) {
            $this->addError('twoFaCode', 'Código 2FA não encontrado. Reinicie o processo.');
            return;
        }

        $google2fa = new Google2FAQRCode();

        if (! $google2fa->verifyKey($secret, $this->twoFaCode)) {
            $this->addError('twoFaCode', 'Código inválido. Verifique o aplicativo e tente novamente.');
            return;
        }

        // Gera códigos de recuperação
        $this->newRecoveryCodes = $this->generateRecoveryCodes();

        $user->two_factor_recovery_codes = $this->newRecoveryCodes;
        $user->two_factor_confirmed_at   = now();
        $user->save();

        ActivityLog::create([
            'organization_id' => $user->organization_id,
            'user_id'         => $user->id,
            'event'           => 'security.2fa_enabled',
            'description'     => 'Autenticação de dois fatores ativada.',
            'subject_type'    => User::class,
            'subject_id'      => (string) $user->id,
            'ip_address'      => request()->ip(),
        ]);

        $this->twoFaEnabled = true;
        $this->twoFaCode    = '';
        $this->twoFaStep    = 'show-codes';
    }

    public function cancelTwoFa(): void
    {
        $user = Auth::user();

        // Limpa configuração não confirmada
        if ($user->two_factor_confirmed_at === null) {
            $user->two_factor_secret         = null;
            $user->two_factor_recovery_codes = null;
            $user->save();

            unset($this->qrCodeSvg, $this->manualKey);
        }

        $this->twoFaCode    = '';
        $this->twoFaStep    = 'idle';
        $this->resetValidation();
    }

    public function doneWithCodes(): void
    {
        $this->newRecoveryCodes = [];
        $this->twoFaStep        = 'idle';
    }

    // =========================================================================
    // 2FA — Desativar
    // =========================================================================

    public function startDisableTwoFa(): void
    {
        $this->twoFaCode = '';
        $this->resetValidation();
        $this->twoFaStep = 'disable';
    }

    public function disableTwoFa(): void
    {
        $this->validate(
            ['twoFaCode' => ['required', 'string']],
            ['twoFaCode.required' => 'Digite o código para confirmar a desativação.']
        );

        $user   = Auth::user();
        $secret = $user->two_factor_secret;

        if (! $secret) {
            $this->addError('twoFaCode', 'Nenhum 2FA configurado.');
            return;
        }

        $google2fa = new Google2FAQRCode();

        // Aceita código TOTP ou código de recuperação
        $validTotp    = $google2fa->verifyKey($secret, $this->twoFaCode);
        $validRecovery = $this->verifyAndConsumeRecoveryCode($user, $this->twoFaCode);

        if (! $validTotp && ! $validRecovery) {
            $this->addError('twoFaCode', 'Código inválido. Use um código TOTP ou de recuperação.');
            return;
        }

        $user->two_factor_secret         = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at   = null;
        $user->save();

        ActivityLog::create([
            'organization_id' => $user->organization_id,
            'user_id'         => $user->id,
            'event'           => 'security.2fa_disabled',
            'description'     => '2FA desativado.',
            'subject_type'    => User::class,
            'subject_id'      => (string) $user->id,
            'ip_address'      => request()->ip(),
        ]);

        $this->twoFaEnabled = false;
        $this->twoFaCode    = '';
        $this->twoFaStep    = 'idle';
        $this->resetValidation();

        unset($this->qrCodeSvg, $this->manualKey);

        $this->dispatch('show-security-toast', message: '2FA desativado com sucesso.', type: 'warning');
    }

    public function regenerateRecoveryCodes(): void
    {
        $user = Auth::user();

        if (! $user->two_factor_confirmed_at) {
            return;
        }

        $this->newRecoveryCodes          = $this->generateRecoveryCodes();
        $user->two_factor_recovery_codes = $this->newRecoveryCodes;
        $user->save();

        ActivityLog::create([
            'organization_id' => $user->organization_id,
            'user_id'         => $user->id,
            'event'           => 'security.recovery_codes_regenerated',
            'description'     => 'Códigos de recuperação do 2FA regenerados.',
            'subject_type'    => User::class,
            'subject_id'      => (string) $user->id,
            'ip_address'      => request()->ip(),
        ]);

        $this->twoFaStep = 'show-codes';
    }

    // =========================================================================
    // Sessões
    // =========================================================================

    public function revokeOtherSessions(): void
    {
        $this->validate(
            ['sessionPassword' => ['required', 'string']],
            ['sessionPassword.required' => 'Informe sua senha para encerrar as outras sessões.']
        );

        $user = Auth::user();

        if (! Hash::check($this->sessionPassword, $user->password)) {
            $this->addError('sessionPassword', 'Senha incorreta.');
            return;
        }

        // Invalida todas as outras sessões atualizando remember_token
        $user->remember_token = Str::random(60);
        $user->save();

        // Re-gera a sessão atual para preservá-la
        request()->session()->regenerate();

        ActivityLog::create([
            'organization_id' => $user->organization_id,
            'user_id'         => $user->id,
            'event'           => 'security.sessions_revoked',
            'description'     => 'Outras sessões encerradas.',
            'subject_type'    => User::class,
            'subject_id'      => (string) $user->id,
            'ip_address'      => request()->ip(),
        ]);

        $this->sessionPassword = '';
        $this->resetValidation();

        $this->dispatch('show-security-toast', message: 'Outras sessões encerradas com sucesso.', type: 'success');
    }

    // =========================================================================
    // Política de senha (admin-only)
    // =========================================================================

    public function savePasswordPolicy(): void
    {
        if (! $this->canManagePolicy) {
            return;
        }

        $this->validate([
            'policyMinLength' => ['required', 'integer', 'min:6', 'max:64'],
            'policyExpiry'    => ['required', 'integer', 'min:0', 'max:365'],
        ], [
            'policyMinLength.min' => 'O mínimo de caracteres é 6.',
            'policyMinLength.max' => 'O máximo de caracteres é 64.',
            'policyExpiry.max'    => 'O prazo máximo de expiração é 365 dias.',
        ]);

        $org = Auth::user()->organization;

        if (! $org) {
            return;
        }

        $org->password_policy = [
            'enabled'     => $this->policyEnabled,
            'min_length'  => $this->policyMinLength,
            'uppercase'   => $this->policyUppercase,
            'numbers'     => $this->policyNumbers,
            'symbols'     => $this->policySymbols,
            'expiry_days' => $this->policyExpiry,
        ];

        $org->save();

        ActivityLog::create([
            'organization_id' => $org->id,
            'user_id'         => Auth::id(),
            'event'           => 'security.password_policy_updated',
            'description'     => 'Política de senha do escritório atualizada.',
            'subject_type'    => Organization::class,
            'subject_id'      => (string) $org->id,
            'ip_address'      => request()->ip(),
        ]);

        $this->policySaved = true;
        $this->dispatch('show-security-toast', message: 'Política de senha salva.', type: 'success');
    }

    // =========================================================================
    // LGPD
    // =========================================================================

    public function requestDataExport(): void
    {
        $user = Auth::user();

        ActivityLog::create([
            'organization_id' => $user->organization_id,
            'user_id'         => $user->id,
            'event'           => 'lgpd.export_requested',
            'description'     => 'Solicitação de exportação de dados pessoais.',
            'subject_type'    => User::class,
            'subject_id'      => (string) $user->id,
            'ip_address'      => request()->ip(),
        ]);

        $this->exportRequested = true;
    }

    public function deleteAccount(): void
    {
        $this->validate(
            ['deletePassword' => ['required', 'string']],
            ['deletePassword.required' => 'Informe sua senha para confirmar a exclusão.']
        );

        $user = Auth::user();

        if (! Hash::check($this->deletePassword, $user->password)) {
            $this->addError('deletePassword', 'Senha incorreta.');
            return;
        }

        ActivityLog::create([
            'organization_id' => $user->organization_id,
            'user_id'         => $user->id,
            'event'           => 'lgpd.account_deletion_requested',
            'description'     => 'Solicitação de exclusão de conta recebida. Processamento em até 30 dias.',
            'subject_type'    => User::class,
            'subject_id'      => (string) $user->id,
            'ip_address'      => request()->ip(),
        ]);

        // Desativa imediatamente e remove do escritório (não deleta o registro)
        $user->is_active       = false;
        $user->organization_id = null;
        $user->syncRoles([]);
        $user->save();

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->redirect(route('login'), navigate: false);
    }

    // =========================================================================
    // Helpers privados
    // =========================================================================

    private function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)))
            ->all();
    }

    private function verifyAndConsumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes;

        if (empty($codes)) {
            return false;
        }

        $normalized = strtoupper(str_replace(' ', '-', trim($code)));

        foreach ($codes as $index => $stored) {
            if (hash_equals(strtoupper($stored), $normalized)) {
                // Consume o código (não pode ser reutilizado)
                unset($codes[$index]);
                $user->two_factor_recovery_codes = array_values($codes);
                $user->save();

                return true;
            }
        }

        return false;
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
        return view('livewire.admin.configuracoes.seguranca');
    }
}
