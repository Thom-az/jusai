@extends('layouts.app')

@section('title', 'Configurações — Perfil')

@push('scripts')
    @vite(['resources/js/modules/configuracoes-alpine.js'])
@endpush

@push('styles')
    @vite(['resources/css/modules/configuracoes.css'])
@endpush

@section('content')
    <div class="settings-shell">

        <x-settings-sidebar :current="$current" />

        <div class="settings-content" x-data="{
            perfilSaved: false,
            perfilPreview: null,
            perfilDirty: false,
            perfilAvatarChanged: false,
            senhaSaved: false,
            showCurrent: false,
            showNew: false,
            showConfirm: false,
            newPassword: '',
            strength: { score:0, color:'var(--jusai-border,#d7dce5)', label:'', hint:'' },
            req: { length:false, upper:false, number:false, symbol:false },
            init() {
                $wire.$on('perfil-saved', () => {
                    this.perfilSaved = true;
                    this.perfilDirty = false;
                    this.perfilAvatarChanged = false;
                    this.perfilPreview = null;
                    setTimeout(() => { this.perfilSaved = false; }, 4000);
                });
                $wire.$on('password-saved', () => {
                    this.senhaSaved = true;
                    this.newPassword = '';
                    this.req = { length:false, upper:false, number:false, symbol:false };
                    this.strength = { score:0, color:'var(--jusai-border,#d7dce5)', label:'', hint:'' };
                    setTimeout(() => { this.senhaSaved = false; }, 4000);
                });
            },
            markDirty() { this.perfilDirty = true; },
            onAvatarSelected(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.perfilAvatarChanged = true;
                const reader = new FileReader();
                reader.onload = (e) => { this.perfilPreview = e.target.result; };
                reader.readAsDataURL(file);
            },
            maskPhone(v) {
                v = v.replace(/\D/g,'').slice(0,11);
                return v.length <= 10
                    ? v.replace(/^(\d{2})(\d{4})(\d{0,4})/,'($1) $2-$3').replace(/-$/,'')
                    : v.replace(/^(\d{2})(\d{5})(\d{0,4})/,'($1) $2-$3').replace(/-$/,'');
            },
            maskOabNumber(v) {
                return v.replace(/\D/g,'').slice(0,6).replace(/(\d{3})(\d{0,3})/,'$1.$2').replace(/\.$/,'');
            },
            updateStrength() {
                const p = this.newPassword;
                this.req = { length:p.length>=8, upper:/[A-Z]/.test(p), number:/[0-9]/.test(p), symbol:/[^A-Za-z0-9]/.test(p) };
                const s = Object.values(this.req).filter(Boolean).length;
                const levels = [
                    { color:'var(--jusai-border,#d7dce5)', label:'', hint:'' },
                    { color:'#ef4444', label:'Fraca', hint:'Adicione maiúsculas, números e símbolos' },
                    { color:'#f97316', label:'Razoável', hint:'Adicione mais caracteres especiais' },
                    { color:'#eab308', label:'Boa', hint:'Quase lá!' },
                    { color:'#22c55e', label:'Forte', hint:'Senha segura!' }
                ];
                this.strength = { score:s, ...levels[s] };
            }
        }">

            <div class="settings-section-header">
                <h2 class="fw-semibold mb-1" style="font-size: 1.2rem;">Perfil</h2>
                <p class="text-secondary mb-0 small">Suas informações pessoais, foto, número da OAB e senha.</p>
            </div>

            <livewire:admin.configuracoes.perfil lazy />

        </div>
    </div>
@endsection
