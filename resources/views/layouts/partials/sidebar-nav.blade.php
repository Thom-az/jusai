<nav class="d-grid gap-2">
    @foreach ($items as $item)
        @php $isActive = request()->is($item['pattern']); @endphp
        <a
            href="{{ route($item['route']) }}"
            wire:navigate.hover
            class="sidebar-link {{ $isActive ? 'active' : '' }}"
            title="{{ $item['label'] }}"
            data-bs-toggle="tooltip"
            data-bs-placement="right"
            @if($isActive) aria-current="page" @endif
        >
            <span class="sidebar-link-icon">
                <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
            </span>
            <span class="sidebar-link-label fw-medium">{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
