<?php

namespace App\Livewire\Admin\Configuracoes;

use App\Models\Organization;
use App\Rules\ValidCnpj;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Escritorio extends Component
{
    use WithFileUploads;

    // -------------------------------------------------------------------------
    // Identidade
    // -------------------------------------------------------------------------
    public string $name      = ''; // nome fantasia
    public string $legalName = ''; // razão social

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $logoUpload     = null;
    public $logoDarkUpload = null;

    // -------------------------------------------------------------------------
    // Dados legais e contato
    // -------------------------------------------------------------------------
    public string $document = ''; // CNPJ formatado
    public string $phone    = '';
    public string $email    = '';

    // -------------------------------------------------------------------------
    // Endereço
    // -------------------------------------------------------------------------
    public string $zipCode      = '';
    public string $street       = '';
    public string $streetNumber = '';
    public string $complement   = '';
    public string $neighborhood = '';
    public string $city         = '';
    public string $state        = '';

    // -------------------------------------------------------------------------
    // Áreas de atuação
    // -------------------------------------------------------------------------
    public array $practiceAreas = [];

    public array $availablePracticeAreas = [
        'Cível', 'Trabalhista', 'Tributário', 'Criminal', 'Família',
        'Empresarial', 'Previdenciário', 'Imobiliário',
        'Consumidor', 'Administrativo', 'Ambiental',
    ];

    // -------------------------------------------------------------------------
    // Permissão e UFs
    // -------------------------------------------------------------------------
    public bool $canEdit = false;

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
        $this->canEdit = Auth::user()->can('manage-firm');

        $org = Auth::user()->organization;

        if (! $org) {
            return;
        }

        $this->name         = $org->name ?? '';
        $this->legalName    = $org->legal_name ?? '';
        $this->document     = $org->document ?? '';
        $this->phone        = $org->phone ?? '';
        $this->email        = $org->email ?? '';
        $this->zipCode      = $org->zip_code ?? '';
        $this->street       = $org->street ?? '';
        $this->streetNumber = $org->street_number ?? '';
        $this->complement   = $org->complement ?? '';
        $this->neighborhood = $org->neighborhood ?? '';
        $this->city         = $org->city ?? '';
        $this->state        = $org->state ?? '';
        $this->practiceAreas = $org->practice_areas ?? [];
    }

    // -------------------------------------------------------------------------
    // Toggle de área de atuação
    // -------------------------------------------------------------------------

    public function toggleArea(string $area): void
    {
        if (! $this->canEdit) {
            return;
        }

        if (in_array($area, $this->practiceAreas, true)) {
            $this->practiceAreas = array_values(
                array_filter($this->practiceAreas, fn ($a) => $a !== $area)
            );
        } else {
            $this->practiceAreas[] = $area;
        }
    }

    // -------------------------------------------------------------------------
    // Salvar
    // -------------------------------------------------------------------------

    public function salvar(): void
    {
        if (! $this->canEdit) {
            $this->addError('geral', 'Você não tem permissão para editar os dados do escritório.');
            return;
        }

        $this->validate([
            'name'         => ['required', 'string', 'max:255'],
            'legalName'    => ['nullable', 'string', 'max:255'],
            'document'     => ['required', new ValidCnpj()],
            'phone'        => ['nullable', 'string', 'max:20'],
            'email'        => ['nullable', 'email:rfc', 'max:255'],
            'zipCode'      => ['nullable', 'string', 'max:9'],
            'street'       => ['nullable', 'string', 'max:255'],
            'streetNumber' => ['nullable', 'string', 'max:20'],
            'complement'   => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:150'],
            'city'         => ['nullable', 'string', 'max:150'],
            'state'        => ['nullable', 'string', 'size:2'],
            'practiceAreas' => ['nullable', 'array'],
            'practiceAreas.*' => ['string', 'in:' . implode(',', $this->availablePracticeAreas)],
            'logoUpload'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:3072'],
            'logoDarkUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:3072'],
        ], [
            'name.required'    => 'O nome fantasia é obrigatório.',
            'document.required' => 'O CNPJ é obrigatório.',
            'email.email'      => 'Informe um email comercial válido.',
            'logoUpload.max'   => 'O logotipo não pode ultrapassar 3 MB.',
        ]);

        $org = Auth::user()->organization;

        // Upload de logotipo (versão clara)
        if ($this->logoUpload) {
            if ($org->logo) {
                Storage::disk('public')->delete($org->logo);
            }
            $org->logo = $this->logoUpload->store('logos', 'public');
            $this->logoUpload = null;
        }

        // Upload de logotipo (versão escura)
        if ($this->logoDarkUpload) {
            if ($org->logo_dark) {
                Storage::disk('public')->delete($org->logo_dark);
            }
            $org->logo_dark = $this->logoDarkUpload->store('logos', 'public');
            $this->logoDarkUpload = null;
        }

        $org->name          = trim($this->name);
        $org->legal_name    = trim($this->legalName) ?: null;
        $org->document      = preg_replace('/\D/', '', $this->document); // salva só dígitos
        $org->phone         = $this->phone ?: null;
        $org->email         = $this->email ?: null;
        $org->zip_code      = $this->zipCode ?: null;
        $org->street        = $this->street ?: null;
        $org->street_number = $this->streetNumber ?: null;
        $org->complement    = $this->complement ?: null;
        $org->neighborhood  = $this->neighborhood ?: null;
        $org->city          = $this->city ?: null;
        $org->state         = $this->state ?: null;
        $org->practice_areas = $this->practiceAreas ?: null;
        $org->save();

        // Re-formata CNPJ após salvar (salvo só dígitos)
        $this->document = $this->formatCnpj($org->document);

        $this->dispatch('escritorio-saved');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function formatCnpj(string $cnpj): string
    {
        $d = preg_replace('/\D/', '', $cnpj);
        if (strlen($d) !== 14) {
            return $cnpj;
        }
        return vsprintf('%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s', str_split($d));
    }

    // -------------------------------------------------------------------------

    public function render()
    {
        return view('livewire.admin.configuracoes.escritorio');
    }
}
