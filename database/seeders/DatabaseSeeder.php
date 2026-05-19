<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $superAdmin = User::create([
            'name' => 'Admin JusAI',
            'email' => 'admin@jusai.com.br',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'organization_id' => null,
            'is_active' => true,
        ]);

        $org = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'Escritório Silva & Associados',
            'slug' => 'silva-associados',
            'email' => 'contato@silva-associados.adv.br',
            'phone' => '(11) 99999-0000',
            'document' => '12.345.678/0001-90',
            'status' => 'active',
            'plan' => 'professional',
        ]);

        $orgAdmin = User::create([
            'name' => 'Dr. Carlos Silva',
            'email' => 'carlos@silva-associados.adv.br',
            'password' => Hash::make('password'),
            'role' => 'org_admin',
            'organization_id' => $org->id,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Ana Beatriz Lima',
            'email' => 'ana@silva-associados.adv.br',
            'password' => Hash::make('password'),
            'role' => 'lawyer',
            'organization_id' => $org->id,
            'is_active' => true,
        ]);

        Subscription::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $org->id,
            'plan' => 'professional',
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'price_cents' => 39700,
            'currency' => 'BRL',
            'current_period_start' => now()->startOfMonth(),
            'current_period_end' => now()->endOfMonth(),
        ]);

        SupportTicket::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $org->id,
            'opened_by' => $orgAdmin->id,
            'title' => 'Dúvida sobre exportação de documentos',
            'description' => 'Como faço para exportar todos os documentos de um caso em PDF?',
            'status' => 'aberto',
            'priority' => 'media',
            'category' => 'duvida',
        ]);

        Lead::create([
            'id' => (string) Str::uuid(),
            'name' => 'Rafaela Mendes',
            'email' => 'rafaela@mendesadvocacia.com.br',
            'phone' => '(21) 98888-1111',
            'company_name' => 'Mendes Advocacia',
            'company_size' => 'pequeno',
            'source' => 'linkedin',
            'status' => 'qualificado',
            'estimated_value_cents' => 59700,
            'assigned_to' => $superAdmin->id,
        ]);

        Lead::create([
            'id' => (string) Str::uuid(),
            'name' => 'Fernando Costa',
            'email' => 'fernando@costaefreitas.adv.br',
            'company_name' => 'Costa & Freitas Advocacia',
            'company_size' => 'medio',
            'source' => 'website',
            'status' => 'demo_agendada',
            'estimated_value_cents' => 119700,
            'assigned_to' => $superAdmin->id,
        ]);
    }
}
