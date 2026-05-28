<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permissões granulares do sistema JusAI (nível de escritório).
     *
     * Não amarrar tudo ao role — as permissões podem ser atribuídas
     * individualmente quando necessário.
     */
    private array $permissions = [
        // Escritório
        'manage-firm',           // Editar dados do escritório
        'view-firm',             // Ver dados do escritório

        // Equipe
        'manage-team',           // Convidar, editar, desativar usuários
        'view-team',             // Ver lista de usuários

        // Faturamento / Plano
        'manage-billing',        // Gerenciar plano e faturamento
        'view-billing',          // Ver plano e uso

        // Segurança
        'manage-security-policy', // Definir políticas de senha e segurança

        // Casos
        'manage-cases',          // Criar e editar casos
        'view-all-cases',        // Ver todos os casos do escritório (não só os próprios)

        // Documentos
        'manage-documents',      // Upload, editar, excluir documentos

        // IA
        'use-ai-analysis',       // Usar análise de IA

        // Templates / Minutas
        'manage-templates',      // Gerenciar templates de minutas
    ];

    /**
     * Atribuição padrão por role.
     * Roles correspondem a papéis dentro de um escritório de advocacia.
     */
    private array $rolePermissions = [
        'admin' => '*', // Todas as permissões

        'socio' => [
            'view-firm',
            'view-team',
            'view-billing',
            'manage-cases',
            'view-all-cases',
            'manage-documents',
            'use-ai-analysis',
            'manage-templates',
        ],

        'advogado' => [
            'manage-cases',      // Somente próprios (regra de negócio no código)
            'manage-documents',
            'use-ai-analysis',
        ],

        'estagiario' => [
            'manage-cases',      // Somente próprios, sem deletar (regra no código)
            'use-ai-analysis',   // Com limite (regra no código)
        ],

        'secretario' => [
            'manage-cases',      // Somente atribuição/agenda (regra no código)
            'view-team',
        ],

        'financeiro' => [
            'view-billing',
            'manage-billing',
        ],
    ];

    public function run(): void
    {
        // Limpar cache de permissões antes de criar
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar todas as permissões
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Criar roles e atribuir permissões
        foreach ($this->rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($perms === '*') {
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($perms);
            }
        }

        $this->command->info('✅ Roles criadas: ' . implode(', ', array_keys($this->rolePermissions)));
        $this->command->info('✅ Permissões criadas: ' . count($this->permissions));
    }
}
