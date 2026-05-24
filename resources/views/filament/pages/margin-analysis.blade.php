<x-filament-panels::page>
    <div class="flex flex-col gap-y-6">
        {{-- 1. Form Filter (Sekarang di paling atas) --}}
        <form wire:submit="applyFilters">
            {{ $this->filtersForm }}
        </form>
    </div>
</x-filament-panels::page>