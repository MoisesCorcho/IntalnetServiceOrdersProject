<x-filament-panels::page>
    @livewire(\App\Filament\Widgets\ServiceOrderStatusCards::class)

    {{-- Renderiza el widget de la tabla en un nuevo contenedor, forzándolo a ocupar todo el ancho. --}}
    <div class="mt-6">
        @livewire(\App\Filament\Widgets\ServiceOrdersTable::class, ['status' => $this->status])
    </div>
</x-filament-panels::page>
