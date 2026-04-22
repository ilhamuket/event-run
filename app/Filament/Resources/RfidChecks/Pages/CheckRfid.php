<?php

namespace App\Filament\Resources\RfidChecks\Pages;

use App\Filament\Resources\RfidChecks\RfidCheckResource;
use App\Models\ParticipantRfidMapping;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

class CheckRfid extends Page
{
    protected static string $resource = RfidCheckResource::class;

     public function getView(): string
    {
        return 'filament.pages.check-rfid';
    }

    public string $rfid_input = '';

    public ?array $result = null;

    public bool $found = false;

    public bool $searched = false;

    public function mount(): void
    {
        $this->rfid_input = '';
        $this->result = null;
        $this->found = false;
        $this->searched = false;
    }

    public function updatedRfidInput(string $value): void
    {
        // Auto-search ketika input berubah (misalnya dari scanner)
        if (strlen(trim($value)) >= 3) {
            $this->searchRfid();
        }
    }

    public function searchRfid(): void
    {
        $tag = trim($this->rfid_input);

        if (empty($tag)) {
            $this->result = null;
            $this->found = false;
            $this->searched = false;
            return;
        }

        $this->searched = true;

        /** @var ParticipantRfidMapping|null $mapping */
        $mapping = ParticipantRfidMapping::with([
            'participant.event',
            'participant.category',
            'assignedBy',
        ])
            ->where('rfid_tag', $tag)
            ->first();

        if ($mapping) {
            $this->found = true;
            $this->result = [
                'rfid_tag'       => $mapping->rfid_tag,
                'is_active'      => $mapping->is_active,
                'assigned_at'    => $mapping->assigned_at?->format('d M Y H:i'),
                'assigned_by'    => $mapping->assignedBy?->name ?? '-',
                'bib'            => $mapping->participant?->bib ?? '-',
                'name'           => $mapping->participant?->name ?? '-',
                'event'          => $mapping->participant?->event?->name ?? '-',
                'category'       => $mapping->participant?->category?->name ?? '-',
                'notes'          => $mapping->notes ?? '-',
            ];
        } else {
            $this->found = false;
            $this->result = null;
        }
    }

    public function resetForm(): void
    {
        $this->rfid_input = '';
        $this->result = null;
        $this->found = false;
        $this->searched = false;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
