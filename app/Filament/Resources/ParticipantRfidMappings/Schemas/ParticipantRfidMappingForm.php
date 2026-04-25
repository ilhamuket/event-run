<?php

namespace App\Filament\Resources\ParticipantRfidMappings\Schemas;

use App\Models\Participant;
use App\Models\ParticipantRfidMapping;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ParticipantRfidMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('RFID Assignment')
                    ->schema([
                        Select::make('participant_id')
                            ->label('Participant')
                            ->options(function () {
                                return Participant::with(['event', 'category', 'activeRfidMappings'])
                                    ->get()
                                    ->mapWithKeys(function ($participant) {
                                        $activeCount = $participant->activeRfidMappings->count();
                                        $tagInfo = $activeCount > 0
                                            ? " [{$activeCount} tag aktif]"
                                            : ' [belum ada tag]';

                                        $label = sprintf(
                                            '[%s] %s - %s (%s)%s',
                                            $participant->bib ?? 'No BIB',
                                            $participant->name,
                                            $participant->event->name ?? 'No Event',
                                            $participant->category->name ?? 'No Category',
                                            $tagInfo
                                        );
                                        return [$participant->id => $label];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('Semua participant ditampilkan. Satu participant bisa punya lebih dari satu RFID tag.')
                            ->afterStateUpdated(fn ($state, callable $set) => $set('_existing_tags', null)),

                        // Info tags aktif yang sudah dimiliki participant yang dipilih
                        Placeholder::make('_existing_tags')
                            ->label('Tag RFID Aktif Saat Ini')
                            ->content(function ($get) {
                                $participantId = $get('participant_id');
                                if (!$participantId) {
                                    return new HtmlString('<span class="text-sm text-gray-400">— Pilih participant dulu —</span>');
                                }

                                $tags = ParticipantRfidMapping::where('participant_id', $participantId)
                                    ->where('is_active', true)
                                    ->pluck('rfid_tag');

                                if ($tags->isEmpty()) {
                                    return new HtmlString('<span class="text-sm text-gray-400">Belum ada tag aktif</span>');
                                }

                                $badges = $tags->map(fn ($tag) =>
                                    "<span class='inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mr-1'>{$tag}</span>"
                                )->join('');

                                return new HtmlString($badges);
                            })
                            ->columnSpanFull(),

                        TextInput::make('rfid_tag')
                            ->label('RFID Tag Baru')
                            ->required()
                            ->unique(
                                table: 'participant_rfid_mappings',
                                column: 'rfid_tag',
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule) => $rule->where('is_active', true)
                            )
                            ->maxLength(255)
                            ->placeholder('Scan atau ketik RFID tag')
                            ->helperText('Tag ini harus unik — satu tag tidak boleh aktif di lebih dari satu participant'),

                        DateTimePicker::make('assigned_at')
                            ->label('Assigned At')
                            ->default(now())
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Nonaktifkan untuk melepas tag tanpa menghapus riwayat'),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->maxLength(500)
                            ->placeholder('Misal: Tag cadangan, tag pengganti karena rusak, dll.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
