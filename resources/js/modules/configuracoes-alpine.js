/**
 * Alpine.data registrations for the configuracoes section.
 *
 * Loaded BEFORE @livewireScripts via @push('scripts') in configuracoes wrapper views.
 * The alpine:init listener fires when Livewire initialises Alpine, guaranteeing all
 * component factories are in the registry before any lazy-loaded Livewire component
 * attempts x-data="someName()" resolution — eliminating the race condition between
 * morphdom patching and Alpine.data registration.
 */
document.addEventListener('alpine:init', () => {

    // ─── Perfil page — scope lives on the WRAPPER (outside the lazy-loaded
    //     Livewire component) so Alpine never needs to initialise x-data during
    //     morphdom. The Livewire blade has NO x-data; all directives inherit here.
    Alpine.data('perfilPageState', () => ({
        // perfil form
        perfilSaved: false,
        perfilDirty: false,
        perfilAvatarChanged: false,
        perfilPreview: null,
        // senha form
        senhaSaved: false,
        showCurrent: false,
        showNew: false,
        showConfirm: false,
        newPassword: '',
        strength: { score: 0, color: 'var(--jusai-border, #d7dce5)', label: '', hint: '' },
        req: { length: false, upper: false, number: false, symbol: false },

        init() {
            window.addEventListener('profile-saved', () => {
                this.perfilSaved         = true;
                this.perfilDirty         = false;
                this.perfilAvatarChanged = false;
                this.perfilPreview       = null;
                setTimeout(() => { this.perfilSaved = false; }, 4000);
            });
            window.addEventListener('password-saved', () => {
                this.senhaSaved  = true;
                this.newPassword = '';
                this.req         = { length: false, upper: false, number: false, symbol: false };
                this.strength    = { score: 0, color: 'var(--jusai-border, #d7dce5)', label: '', hint: '' };
                setTimeout(() => { this.senhaSaved = false; }, 4000);
            });
            window.addEventListener('pageshow', (e) => {
                if (e.persisted) { this.perfilSaved = false; this.senhaSaved = false; }
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

        updateStrength() {
            const p = this.newPassword;
            this.req = {
                length: p.length >= 8,
                upper:  /[A-Z]/.test(p),
                number: /[0-9]/.test(p),
                symbol: /[^A-Za-z0-9]/.test(p),
            };
            const s = Object.values(this.req).filter(Boolean).length;
            const levels = [
                { color: 'var(--jusai-border, #d7dce5)', label: '',        hint: '' },
                { color: '#ef4444', label: 'Fraca',    hint: 'Adicione maiúsculas, números e símbolos' },
                { color: '#f97316', label: 'Razoável', hint: 'Adicione mais caracteres especiais' },
                { color: '#eab308', label: 'Boa',      hint: 'Quase lá!' },
                { color: '#22c55e', label: 'Forte',    hint: 'Senha segura!' },
            ];
            this.strength = { score: s, ...levels[s] };
        },

        maskPhone(v) {
            v = v.replace(/\D/g, '').slice(0, 11);
            if (v.length === 0) return '';
            if (v.length <= 2)  return `(${v}`;
            if (v.length <= 6)  return `(${v.slice(0,2)}) ${v.slice(2)}`;
            if (v.length <= 10) return `(${v.slice(0,2)}) ${v.slice(2,6)}-${v.slice(6)}`;
            return `(${v.slice(0,2)}) ${v.slice(2,7)}-${v.slice(7)}`;
        },

        maskOabNumber(v) {
            v = v.replace(/\D/g, '').slice(0, 6);
            if (v.length <= 3) return v;
            return `${v.slice(0,3)}.${v.slice(3)}`;
        },
    }));

    // ─── Perfil (legacy named refs — kept for Alpine.data registry) ──────────

    Alpine.data('perfilForm', () => ({
        preview: null,
        avatarChanged: false,
        saved: false,

        init() {
            window.addEventListener('profile-saved', () => {
                this.avatarChanged = false;
                this.preview       = null;
                this.saved         = true;
                setTimeout(() => { this.saved = false; }, 4000);
            });
            window.addEventListener('pageshow', (e) => { if (e.persisted) this.saved = false; });
        },

        get isDirty() {
            const o = this.$wire.original ?? {};
            return (this.$wire.name      ?? '') !== (o.name      ?? '')
                || (this.$wire.email     ?? '') !== (o.email     ?? '')
                || (this.$wire.phone     ?? '') !== (o.phone     ?? '')
                || (this.$wire.oabNumber ?? '') !== (o.oabNumber ?? '')
                || (this.$wire.oabUf     ?? '') !== (o.oabUf     ?? '')
                || (this.$wire.jobTitle  ?? '') !== (o.jobTitle  ?? '');
        },

        markDirty() {},

        onAvatarSelected(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.avatarChanged = true;
            const reader = new FileReader();
            reader.onload = (e) => { this.preview = e.target.result; };
            reader.readAsDataURL(file);
        },

        maskPhone(value) {
            value = value.replace(/\D/g, '').slice(0, 11);
            if (value.length === 0) return '';
            if (value.length <= 2)  return `(${value}`;
            if (value.length <= 6)  return `(${value.slice(0,2)}) ${value.slice(2)}`;
            if (value.length <= 10) return `(${value.slice(0,2)}) ${value.slice(2,6)}-${value.slice(6)}`;
            return `(${value.slice(0,2)}) ${value.slice(2,7)}-${value.slice(7)}`;
        },

        maskOabNumber(value) {
            value = value.replace(/\D/g, '').slice(0, 6);
            if (value.length <= 3) return value;
            return `${value.slice(0,3)}.${value.slice(3)}`;
        },
    }));

    Alpine.data('senhaForm', () => ({
        saved: false,
        showCurrent: false,
        showNew: false,
        showConfirm: false,
        newPassword: '',
        strength: { score: 0, color: 'var(--jusai-border, #d7dce5)', label: '', hint: '' },
        req: { length: false, upper: false, number: false, symbol: false },

        init() {
            window.addEventListener('password-saved', () => {
                this.saved       = true;
                this.newPassword = '';
                this.req         = { length: false, upper: false, number: false, symbol: false };
                this.strength    = { score: 0, color: 'var(--jusai-border, #d7dce5)', label: '', hint: '' };
                setTimeout(() => { this.saved = false; }, 4000);
            });
            window.addEventListener('pageshow', (e) => { if (e.persisted) this.saved = false; });
        },

        updateStrength() {
            const p = this.newPassword;
            this.req = {
                length: p.length >= 8,
                upper:  /[A-Z]/.test(p),
                number: /[0-9]/.test(p),
                symbol: /[^A-Za-z0-9]/.test(p),
            };
            const score = Object.values(this.req).filter(Boolean).length;
            const levels = [
                { color: 'var(--jusai-border, #d7dce5)', label: '',        hint: '' },
                { color: '#ef4444',                      label: 'Fraca',   hint: 'Adicione maiúsculas, números e símbolos' },
                { color: '#f97316',                      label: 'Razoável', hint: 'Adicione mais caracteres especiais' },
                { color: '#eab308',                      label: 'Boa',     hint: 'Quase lá!' },
                { color: '#22c55e',                      label: 'Forte',   hint: 'Senha segura!' },
            ];
            this.strength = { score, ...levels[score] };
        },
    }));

    // ─── Escritório ──────────────────────────────────────────────────────────

    Alpine.data('escritorioForm', () => ({
        hasChanges: false,
        logoPreview: null,
        logoDarkPreview: null,
        cepLoading: false,
        cepOk: false,
        cepError: false,

        onSaved() {
            this.hasChanges      = false;
            this.logoPreview     = null;
            this.logoDarkPreview = null;
            window.dispatchEvent(new CustomEvent('app:toast', {
                detail: { message: 'Dados do escritório atualizados com sucesso.', type: 'success' }
            }));
        },

        onLogoSelected(event, type) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                if (type === 'dark') { this.logoDarkPreview = e.target.result; }
                else                 { this.logoPreview     = e.target.result; }
            };
            reader.readAsDataURL(file);
            this.hasChanges = true;
        },

        maskCnpj(value) {
            value = value.replace(/\D/g, '').slice(0, 14);
            if (value.length <=  2) return value;
            if (value.length <=  5) return `${value.slice(0,2)}.${value.slice(2)}`;
            if (value.length <=  8) return `${value.slice(0,2)}.${value.slice(2,5)}.${value.slice(5)}`;
            if (value.length <= 12) return `${value.slice(0,2)}.${value.slice(2,5)}.${value.slice(5,8)}/${value.slice(8)}`;
            return `${value.slice(0,2)}.${value.slice(2,5)}.${value.slice(5,8)}/${value.slice(8,12)}-${value.slice(12)}`;
        },

        maskPhone(value) {
            value = value.replace(/\D/g, '').slice(0, 11);
            if (value.length === 0) return '';
            if (value.length <=  2) return `(${value}`;
            if (value.length <=  6) return `(${value.slice(0,2)}) ${value.slice(2)}`;
            if (value.length <= 10) return `(${value.slice(0,2)}) ${value.slice(2,6)}-${value.slice(6)}`;
            return `(${value.slice(0,2)}) ${value.slice(2,7)}-${value.slice(7)}`;
        },

        maskCep(value) {
            value = value.replace(/\D/g, '').slice(0, 8);
            if (value.length > 5) return `${value.slice(0,5)}-${value.slice(5)}`;
            return value;
        },

        maybeFetchCep(maskedValue) {
            const digits = (maskedValue ?? this.$wire?.zipCode ?? '').replace(/\D/g, '');
            if (digits.length !== 8) { this.cepOk = false; this.cepError = false; return; }
            this.fetchCep(digits);
        },

        async fetchCep(digits) {
            this.cepLoading = true;
            this.cepOk      = false;
            this.cepError   = false;
            try {
                const res  = await fetch(`https://viacep.com.br/ws/${digits}/json/`);
                const data = await res.json();
                if (data.erro) { this.cepError = true; return; }
                await this.$wire.set('street',       data.logradouro || '');
                await this.$wire.set('neighborhood', data.bairro     || '');
                await this.$wire.set('city',         data.localidade || '');
                await this.$wire.set('state',        data.uf         || '');
                this.cepOk      = true;
                this.hasChanges = true;
                this.$nextTick(() => this.$refs.streetNumber?.focus());
            } catch (_) {
                this.cepError = true;
            } finally {
                this.cepLoading = false;
            }
        },
    }));

    // ─── Equipe ──────────────────────────────────────────────────────────────

    Alpine.data('equipeForm', () => ({
        showInviteModal() {
            const el = this.$refs.modalConvite;
            if (el && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(el).show();
            }
        },

        showToast(message, type) {
            window.dispatchEvent(new CustomEvent('app:toast', {
                detail: { message, type: type || 'success' }
            }));
        },
    }));

    // ─── Preferências ────────────────────────────────────────────────────────

    Alpine.data('preferenciasForm', () => ({
        applyTheme(pref) {
            const theme = pref === 'system'
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : pref;
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('jusai.theme', theme);
            const icon = document.getElementById('themeToggleIcon');
            const btn  = document.getElementById('themeToggle');
            if (icon) icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
            if (btn)  btn.setAttribute('aria-label', theme === 'dark' ? 'Mudar para tema claro' : 'Mudar para tema escuro');
        },
    }));

    // ─── Segurança ───────────────────────────────────────────────────────────

    Alpine.data('segurancaForm', () => ({
        showToast(message, type) {
            window.dispatchEvent(new CustomEvent('app:toast', {
                detail: { message, type: type || 'success' }
            }));
        },

        copyText(text) {
            navigator.clipboard?.writeText(text);
        },
    }));

});
