{{--
  Barra de progresso de navegação SPA (Alpine.js + Livewire).
  Inclua uma vez no layout, logo após a abertura do <body>.
  Usa animação determinística 0 → 80 % durante o carregamento, 100 % ao concluir.

  Para desativar o loader indeterminado do app.js junto com este componente, adicione:
  #pageLoader { display: none !important; }
--}}
<div
    x-data="{
        show: false,
        progress: 0,
        _raf: null,

        start() {
            this.show = true;
            this.progress = 0;
            this._tween(80, 600);
        },

        finish() {
            this._tween(100, 220);
            setTimeout(() => { this.show = false; this.progress = 0; }, 420);
        },

        _tween(target, ms) {
            cancelAnimationFrame(this._raf);
            const from = this.progress;
            const diff = target - from;
            const t0 = performance.now();
            const step = (now) => {
                const t = Math.min((now - t0) / ms, 1);
                const ease = t < 0.5 ? 2*t*t : -1 + (4 - 2*t)*t;
                this.progress = from + diff * ease;
                if (t < 1) this._raf = requestAnimationFrame(step);
            };
            this._raf = requestAnimationFrame(step);
        }
    }"
    x-on:livewire:navigating.window="start()"
    x-on:livewire:navigated.window="finish()"
    x-show="show"
    x-transition:enter="transition-opacity duration-100"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="nav-progress-wrap"
    aria-hidden="true"
    role="progressbar"
    :aria-valuenow="Math.round(progress)"
    aria-valuemin="0"
    aria-valuemax="100"
>
    <div class="nav-progress-bar" :style="`width: ${progress}%`"></div>
</div>
