<?php

namespace App\Traits;

/**
 * Helper methods para checagens comuns de permissão dentro do escritório.
 *
 * Requer que o model use o trait HasRoles do Spatie (via User model).
 *
 * Uso:
 *   $user->isAdmin()           → true se tem role 'admin' no escritório
 *   $user->canManageFirm()     → true se tem permissão 'manage-firm'
 *   $user->canManageTeam()     → true se tem permissão 'manage-team'
 *   $user->canViewBilling()    → true se tem permissão 'view-billing'
 *   $user->canManageBilling()  → true se tem permissão 'manage-billing'
 *   $user->canUseAi()          → true se tem permissão 'use-ai-analysis'
 */
trait HasOrgPermissions
{
    /**
     * Verifica se o usuário é administrador do escritório (role 'admin').
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Verifica se o usuário é sócio do escritório.
     */
    public function isSocio(): bool
    {
        return $this->hasRole('socio');
    }

    /**
     * Pode gerenciar os dados do escritório (razão social, CNPJ, logo, etc.).
     */
    public function canManageFirm(): bool
    {
        return $this->can('manage-firm');
    }

    /**
     * Pode visualizar os dados do escritório.
     */
    public function canViewFirm(): bool
    {
        return $this->can('view-firm');
    }

    /**
     * Pode gerenciar a equipe (convidar, editar, desativar usuários).
     */
    public function canManageTeam(): bool
    {
        return $this->can('manage-team');
    }

    /**
     * Pode visualizar a lista de usuários da equipe.
     */
    public function canViewTeam(): bool
    {
        return $this->can('view-team');
    }

    /**
     * Pode gerenciar plano e faturamento.
     */
    public function canManageBilling(): bool
    {
        return $this->can('manage-billing');
    }

    /**
     * Pode visualizar plano e uso.
     */
    public function canViewBilling(): bool
    {
        return $this->can('view-billing');
    }

    /**
     * Pode definir políticas de segurança do escritório (senha, expiração, etc.).
     */
    public function canManageSecurityPolicy(): bool
    {
        return $this->can('manage-security-policy');
    }

    /**
     * Pode criar e editar casos.
     */
    public function canManageCases(): bool
    {
        return $this->can('manage-cases');
    }

    /**
     * Pode visualizar todos os casos do escritório (não só os próprios).
     */
    public function canViewAllCases(): bool
    {
        return $this->can('view-all-cases');
    }

    /**
     * Pode gerenciar documentos.
     */
    public function canManageDocuments(): bool
    {
        return $this->can('manage-documents');
    }

    /**
     * Pode usar análise de IA.
     */
    public function canUseAi(): bool
    {
        return $this->can('use-ai-analysis');
    }

    /**
     * Pode gerenciar templates de minutas.
     */
    public function canManageTemplates(): bool
    {
        return $this->can('manage-templates');
    }

    /**
     * Retorna o label legível do role principal do usuário no escritório.
     */
    public function orgRoleLabel(): string
    {
        $roleLabels = [
            'admin'      => 'Administrador',
            'socio'      => 'Sócio',
            'advogado'   => 'Advogado',
            'estagiario' => 'Estagiário',
            'secretario' => 'Secretário',
            'financeiro' => 'Financeiro',
        ];

        $role = $this->roles->first();

        if (! $role) {
            return 'Sem perfil';
        }

        return $roleLabels[$role->name] ?? ucfirst($role->name);
    }
}
