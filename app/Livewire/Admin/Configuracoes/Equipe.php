<?php

namespace App\Livewire\Admin\Configuracoes;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Equipe extends Component
{
    use WithPagination;

    // ─── Lista / Filtro ────────────────────────────────────────────────────────
    public string $search       = '';
    public string $filterRole   = '';
    public string $filterStatus = '';

    // ─── Permissão ─────────────────────────────────────────────────────────────
    public bool $canManage = false;

    // ─── Modal de Convite ───────────────────────────────────────────────────────
    public string $inviteName     = '';
    public string $inviteEmail    = '';
    public string $inviteRole     = 'advogado';
    public string $inviteJobTitle = '';
    public bool   $inviteDone     = false;
    public string $tempPassword   = '';

    // ─── Drawer de Usuário ──────────────────────────────────────────────────────
    public ?string $selectedUserId   = null;
    public string  $editRole         = '';
    public bool    $showRemoveConfirm = false;

    /** Labels dos roles disponíveis */
    public array $roles = [
        'admin'      => 'Administrador',
        'socio'      => 'Sócio',
        'advogado'   => 'Advogado',
        'estagiario' => 'Estagiário',
        'secretario' => 'Secretário',
        'financeiro'  => 'Financeiro',
    ];

    // ─── Inicialização ──────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->canManage = Auth::user()->can('manage-team');
    }

    // ─── Computed: lista paginada ───────────────────────────────────────────────

    #[Computed]
    public function users()
    {
        $orgId = Auth::user()->organization_id;

        return User::with('roles')
            ->where('organization_id', $orgId)
            ->where('id', '!=', Auth::id())
            ->when($this->search !== '', function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($inner) use ($s) {
                    $inner->where('name', 'like', $s)
                          ->orWhere('email', 'like', $s);
                });
            })
            ->when($this->filterRole !== '', function ($q) {
                $q->whereHas('roles', fn ($r) => $r->where('name', $this->filterRole));
            })
            ->when($this->filterStatus !== '', function ($q) {
                $q->where('is_active', $this->filterStatus === 'active');
            })
            ->orderBy('name')
            ->paginate(10);
    }

    // ─── Computed: usuário selecionado ──────────────────────────────────────────

    #[Computed]
    public function selectedUser(): ?User
    {
        if (! $this->selectedUserId) {
            return null;
        }

        return User::with('roles')->find($this->selectedUserId);
    }

    // ─── Computed: estatísticas do header ──────────────────────────────────────

    #[Computed]
    public function stats(): array
    {
        $orgId = Auth::user()->organization_id;

        $base     = User::where('organization_id', $orgId)->where('id', '!=', Auth::id());
        $total    = $base->count();
        $active   = (clone $base)->where('is_active', true)->count();

        return [
            'total'    => $total,
            'active'   => $active,
            'inactive' => $total - $active,
        ];
    }

    // ─── Reset de paginação nos filtros ────────────────────────────────────────

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterRole(): void   { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    // =========================================================================
    // Modal de Convite
    // =========================================================================

    public function openInvite(): void
    {
        if (! $this->canManage) {
            return;
        }

        $this->resetValidation();
        $this->reset(['inviteName', 'inviteEmail', 'inviteJobTitle', 'inviteDone', 'tempPassword']);
        $this->inviteRole = 'advogado';

        $this->dispatch('open-invite-modal');
    }

    public function convidar(): void
    {
        if (! $this->canManage) {
            return;
        }

        $this->validate([
            'inviteName'     => ['required', 'string', 'max:255'],
            'inviteEmail'    => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'inviteRole'     => ['required', 'in:' . implode(',', array_keys($this->roles))],
            'inviteJobTitle' => ['nullable', 'string', 'max:100'],
        ], [
            'inviteName.required'  => 'O nome é obrigatório.',
            'inviteEmail.required' => 'O e-mail é obrigatório.',
            'inviteEmail.email'    => 'Informe um e-mail válido.',
            'inviteEmail.unique'   => 'Este e-mail já está cadastrado.',
            'inviteRole.required'  => 'Selecione um perfil.',
            'inviteRole.in'        => 'Perfil de acesso inválido.',
        ]);

        // Senha temporária: letras + números, sem símbolos (12 chars)
        $this->tempPassword = Str::password(12, letters: true, numbers: true, symbols: false, spaces: false);

        $plain    = $this->tempPassword;
        $orgId    = Auth::user()->organization_id;
        $actorId  = Auth::id();
        $roleName = $this->inviteRole;
        $roleLabel = $this->roles[$roleName] ?? $roleName;

        $user = DB::transaction(function () use ($plain, $orgId, $actorId, $roleName, $roleLabel) {
            $user = User::create([
                'name'            => trim($this->inviteName),
                'email'           => strtolower(trim($this->inviteEmail)),
                'password'        => Hash::make($plain),
                'organization_id' => $orgId,
                'role'            => 'user',
                'job_title'       => $this->inviteJobTitle ?: null,
                'is_active'       => true,
            ]);

            $user->assignRole($roleName);

            ActivityLog::create([
                'organization_id' => $orgId,
                'user_id'         => $actorId,
                'event'           => 'team.invited',
                'description'     => 'Membro adicionado: ' . $user->email . ' (' . $roleLabel . ')',
                'subject_type'    => User::class,
                'subject_id'      => (string) $user->id,
                'ip_address'      => request()->ip(),
            ]);

            return $user;
        });

        unset($this->users, $this->stats);

        $this->inviteDone = true;
    }

    public function resetInvite(): void
    {
        $this->resetValidation();
        $this->reset(['inviteName', 'inviteEmail', 'inviteJobTitle', 'inviteDone', 'tempPassword']);
        $this->inviteRole = 'advogado';
    }

    // =========================================================================
    // Drawer de Usuário
    // =========================================================================

    public function openDrawer(string $userId): void
    {
        $this->selectedUserId    = $userId;
        $this->showRemoveConfirm = false;
        $this->resetValidation();
        unset($this->selectedUser);

        $this->editRole = $this->selectedUser?->roles->first()?->name ?? '';

        $this->dispatch('open-user-drawer');
    }

    public function closeDrawer(): void
    {
        $this->selectedUserId    = null;
        $this->showRemoveConfirm = false;
        $this->editRole          = '';
        unset($this->selectedUser);

        $this->dispatch('close-user-drawer');
    }

    public function updateRole(): void
    {
        if (! $this->canManage) {
            return;
        }

        $this->validate(
            ['editRole' => ['required', 'in:' . implode(',', array_keys($this->roles))]],
            ['editRole.required' => 'Selecione um perfil.']
        );

        $user = User::find($this->selectedUserId);

        if (! $user || $user->organization_id !== Auth::user()->organization_id) {
            return;
        }

        $oldRole = $user->roles->first()?->name ?? '—';
        $user->syncRoles([$this->editRole]);

        ActivityLog::create([
            'organization_id' => Auth::user()->organization_id,
            'user_id'         => Auth::id(),
            'event'           => 'team.role_changed',
            'description'     => 'Perfil de ' . $user->email . ': ' . $oldRole . ' → ' . $this->editRole,
            'subject_type'    => User::class,
            'subject_id'      => (string) $user->id,
            'ip_address'      => request()->ip(),
        ]);

        unset($this->users, $this->selectedUser, $this->stats);

        $this->dispatch('show-team-toast', message: 'Perfil atualizado com sucesso.', type: 'success');
    }

    public function toggleActive(): void
    {
        if (! $this->canManage) {
            return;
        }

        $user = User::find($this->selectedUserId);

        if (! $user || $user->organization_id !== Auth::user()->organization_id) {
            return;
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $label = $user->is_active ? 'reativado' : 'desativado';

        ActivityLog::create([
            'organization_id' => Auth::user()->organization_id,
            'user_id'         => Auth::id(),
            'event'           => $user->is_active ? 'team.reactivated' : 'team.deactivated',
            'description'     => 'Usuário ' . $label . ': ' . $user->email,
            'subject_type'    => User::class,
            'subject_id'      => (string) $user->id,
            'ip_address'      => request()->ip(),
        ]);

        unset($this->users, $this->selectedUser, $this->stats);

        $this->dispatch('show-team-toast', message: 'Usuário ' . $label . ' com sucesso.', type: 'success');
    }

    public function removeUser(): void
    {
        if (! $this->canManage) {
            return;
        }

        $user = User::find($this->selectedUserId);

        if (! $user || $user->organization_id !== Auth::user()->organization_id) {
            return;
        }

        $email = $user->email;

        // Remove o role Spatie e desvincula do escritório (não deleta a conta)
        $user->syncRoles([]);
        $user->organization_id = null;
        $user->is_active       = false;
        $user->save();

        ActivityLog::create([
            'organization_id' => Auth::user()->organization_id,
            'user_id'         => Auth::id(),
            'event'           => 'team.removed',
            'description'     => 'Usuário removido do escritório: ' . $email,
            'ip_address'      => request()->ip(),
        ]);

        unset($this->users, $this->stats);

        $this->selectedUserId    = null;
        $this->showRemoveConfirm = false;
        $this->editRole          = '';
        unset($this->selectedUser);

        $this->dispatch('close-user-drawer');
        $this->dispatch('show-team-toast', message: $email . ' foi removido do escritório.', type: 'warning');
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
        return view('livewire.admin.configuracoes.equipe');
    }
}
