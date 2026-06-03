<?php

namespace App\Livewire\Admin\Configuracoes;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Perfil extends Component
{
    use WithFileUploads;

    // -------------------------------------------------------------------------
    // Dados pessoais
    // -------------------------------------------------------------------------
    public string $name     = '';
    public string $email    = '';
    public string $phone    = '';
    public string $oabNumber = '';
    public string $oabUf    = '';
    public string $jobTitle = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $avatarUpload = null;

    // -------------------------------------------------------------------------
    // Alterar senha
    // -------------------------------------------------------------------------
    public string $currentPassword         = '';
    public string $newPassword             = '';
    public string $newPasswordConfirmation = '';

    // -------------------------------------------------------------------------
    // Estado da UI
    // -------------------------------------------------------------------------

    /** Dispara evento para Alpine.js mostrar banner de sucesso */
    public bool $profileSaved  = false;
    public bool $passwordSaved = false;

    /** Valores originais para dirty detection (usados no Alpine também) */
    public array $original = [];

    // -------------------------------------------------------------------------
    // Cargos disponíveis no select
    // -------------------------------------------------------------------------
    public array $jobTitles = [
        '' => 'Selecione um cargo...',
        'Sócio' => 'Sócio',
        'Associado' => 'Associado',
        'Advogado Sênior' => 'Advogado Sênior',
        'Advogado Pleno' => 'Advogado Pleno',
        'Advogado Júnior' => 'Advogado Júnior',
        'Estagiário(a)' => 'Estagiário(a)',
        'Secretário(a)' => 'Secretário(a)',
        'Paralegal' => 'Paralegal',
        'Financeiro(a)' => 'Financeiro(a)',
        'Diretor(a) Administrativo(a)' => 'Diretor(a) Administrativo(a)',
    ];

    /** UFs do Brasil para select de OAB */
    public array $ufs = [
        'AC','AL','AM','AP','BA','CE','DF','ES','GO',
        'MA','MG','MS','MT','PA','PB','PE','PI','PR',
        'RJ','RN','RO','RR','RS','SC','SE','SP','TO',
    ];

    // -------------------------------------------------------------------------
    // Inicialização
    // -------------------------------------------------------------------------

    public function mount(): void
    {
        $user = Auth::user();

        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->phone     = $user->phone ?? '';
        $this->oabNumber = $user->oab_number ?? '';
        $this->oabUf     = $user->oab_uf ?? '';
        $this->jobTitle  = $user->job_title ?? '';

        // Valores originais para o dirty-check no Alpine
        $this->original = [
            'name'      => $this->name,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'oabNumber' => $this->oabNumber,
            'oabUf'     => $this->oabUf,
            'jobTitle'  => $this->jobTitle,
        ];
    }

    // -------------------------------------------------------------------------
    // Salvar dados pessoais
    // -------------------------------------------------------------------------

    public function salvarPerfil(): void
    {
        $user = Auth::user();

        // Remove máscara do campo OAB antes de validar (Alpine exibe "123.456", backend espera "123456")
        $this->oabNumber = preg_replace('/\D/', '', $this->oabNumber ?? '');

        $this->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email:rfc', 'max:255', "unique:users,email,{$user->id}"],
            'phone'       => ['nullable', 'string', 'max:20'],
            'oabNumber'   => ['nullable', 'string', 'max:10', 'regex:/^\d{1,6}$/'],
            'oabUf'       => ['nullable', 'string', 'size:2'],
            'jobTitle'    => ['nullable', 'string', 'max:100'],
            'avatarUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required'       => 'O nome completo é obrigatório.',
            'email.required'      => 'O email é obrigatório.',
            'email.unique'        => 'Este email já está em uso por outro usuário.',
            'oabNumber.regex'     => 'O número da OAB deve conter somente dígitos (ex: 123456).',
            'oabUf.size'          => 'Informe a UF com 2 letras (ex: SP).',
            'avatarUpload.image'  => 'O arquivo deve ser uma imagem.',
            'avatarUpload.max'    => 'A imagem não pode ultrapassar 2 MB.',
        ]);

        $oldEmail = $user->email;

        // Avatar: faz upload se novo arquivo foi selecionado
        if ($this->avatarUpload) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $this->avatarUpload->store('avatars', 'public');
            $this->avatarUpload = null;
        }

        $user->name      = trim($this->name);
        $user->email     = trim($this->email);
        $user->phone     = $this->phone    ?: null;
        $user->oab_number = $this->oabNumber ?: null;
        $user->oab_uf    = $this->oabUf    ?: null;
        $user->job_title = $this->jobTitle ?: null;
        $user->save();

        // Audit: email alterado é dado sensível
        if ($oldEmail !== $user->email) {
            $this->registrarAudit('Email de acesso alterado', [
                'old' => ['email' => $oldEmail],
                'new' => ['email' => $user->email],
            ]);
        }

        // Atualiza snapshot para dirty-check
        $this->original = [
            'name'      => $this->name,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'oabNumber' => $this->oabNumber,
            'oabUf'     => $this->oabUf,
            'jobTitle'  => $this->jobTitle,
        ];

        $this->dispatch('profile-saved');
    }

    // -------------------------------------------------------------------------
    // Alterar senha
    // -------------------------------------------------------------------------

    public function salvarSenha(): void
    {
        $this->validate([
            'currentPassword'         => ['required', 'current_password'],
            'newPassword'             => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            ],
            'newPasswordConfirmation' => ['required'],
        ], [
            'currentPassword.required'        => 'Informe sua senha atual.',
            'currentPassword.current_password' => 'A senha atual está incorreta.',
            'newPassword.required'            => 'Informe a nova senha.',
            'newPassword.confirmed'           => 'A confirmação da senha não confere.',
            'newPassword.min'                 => 'A nova senha deve ter pelo menos 8 caracteres.',
            'newPasswordConfirmation.required' => 'Confirme a nova senha.',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($this->newPassword);
        $user->save();

        $this->registrarAudit('Senha alterada pelo usuário', []);

        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
        $this->dispatch('password-saved');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function registrarAudit(string $description, array $metadata): void
    {
        $user = Auth::user();

        try {
            ActivityLog::create([
                'organization_id' => $user->organization_id,
                'user_id'         => $user->id,
                'event'           => 'updated',
                'description'     => $description,
                'subject_type'    => User::class,
                'subject_id'      => (string) $user->id,
                'metadata'        => $metadata,
                'ip_address'      => request()->ip(),
            ]);
        } catch (\Throwable) {
            // Nunca interromper a operação por falha no audit
        }
    }

    // -------------------------------------------------------------------------

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
        return view('livewire.admin.configuracoes.perfil');
    }
}
