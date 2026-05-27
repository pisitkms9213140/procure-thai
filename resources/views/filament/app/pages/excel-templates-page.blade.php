<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($this->getTemplates() as $template)
            <div class="fi-card rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">{{ $template['icon'] }}</span>
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $template['title'] }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $template['description'] }}
                        </p>
                    </div>
                </div>

                <a href="{{ $template['route'] }}"
                   class="fi-btn fi-btn-color-primary fi-btn-size-md inline-flex items-center justify-center gap-2
                          rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm
                          hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 transition-colors">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                    ดาวน์โหลด Template
                </a>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
