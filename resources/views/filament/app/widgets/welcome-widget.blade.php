<x-filament-widgets::widget>
    @php
        $userData = $this->getUserData();
        $roleBadgeClass = match ($userData['role']) {
            'manager'    => 'bg-amber-100 text-amber-800 ring-amber-500/30 dark:bg-amber-400/10 dark:text-amber-300',
            'supervisor' => 'bg-blue-100 text-blue-800 ring-blue-500/30 dark:bg-blue-400/10 dark:text-blue-300',
            default      => 'bg-gray-100 text-gray-600 ring-gray-500/20 dark:bg-gray-400/10 dark:text-gray-400',
        };
    @endphp

    <div class="fi-wi-account flex flex-wrap items-center justify-between gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        {{-- Left: Avatar + Info --}}
        <div class="flex items-center gap-4 min-w-0">
            {{-- Avatar --}}
            @if ($userData['avatar_url'])
                <img src="{{ $userData['avatar_url'] }}"
                     alt="{{ $userData['name'] }}"
                     style="height: 5rem; width: 5rem; object-fit: cover; flex: none;"
                     class="rounded-full ring-2 ring-primary-500/30">
            @else
                <div style="height: 5rem; width: 5rem; flex: none; font-size: 1.5rem;"
                     class="flex items-center justify-center rounded-full bg-primary-500 text-white font-semibold select-none">
                    {{ $userData['initials'] }}
                </div>
            @endif

            {{-- Name / Email / Role --}}
            <div class="min-w-0">
                <p class="text-sm text-gray-500 dark:text-gray-400">ยินดีต้อนรับ</p>
                <p class="truncate text-base font-semibold text-gray-950 dark:text-white">
                    {{ $userData['name'] }}
                </p>
                <span class="mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $roleBadgeClass }}">
                    {{ $userData['role_label'] }}
                </span>
            </div>
        </div>

        {{-- Right: Logout --}}
        <form action="{{ filament()->getLogoutUrl() }}" method="POST">
            @csrf
            <button type="submit"
                    class="fi-btn inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-white/5 transition-colors">
                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                ออกจากระบบ
            </button>
        </form>
    </div>
</x-filament-widgets::widget>
