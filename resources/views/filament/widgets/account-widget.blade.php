@php
    $user = filament()->auth()->user();
    $is_blocked = $user['is_blocked'];
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section icon="heroicon-c-exclamation-triangle">
        <div class="flex items-center gap-x-3">
            <div class="flex-1">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ filament()->getUserName($user) }}, 你被停權了。
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
