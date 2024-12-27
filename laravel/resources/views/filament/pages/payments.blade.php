<x-filament::page>
    <form wire:submit.prevent="submit">
        {{ $this->form }}
    </form>
    <h2>User's Payments</h2>
    {{ $this->table }}
</x-filament::page>
