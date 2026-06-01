<x-filament-panels::page>
    <div @if ($this->hasActiveAnalysisJob()) wire:poll.5s="pollTemaDetalhe" @endif>
        {{ $this->content }}
    </div>
</x-filament-panels::page>
