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
use Illuminate\Support\Facades\DB;
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
                            ->searchable()
                            ->preload(false) // jangan preload semua — biarkan search yang trigger
                            ->getSearchResultsUsing(function (string $search) {
                                // Raw query: satu query, tidak ada model hydration
                                $rows = DB::select(
                                    "SELECT
                                        p.id,
                                        p.bib,
                                        p.name,
                                        e.name  AS event_name,
                                        ec.name AS category_name,
                                        COUNT(CASE WHEN m.is_active = 1 THEN 1 END) AS active_tag_count
                                    FROM participants p
                                    JOIN events e ON e.id = p.event_id
                                    JOIN event_categories ec ON ec.id = p.event_category_id
                                    LEFT JOIN participant_rfid_mappings m ON m.participant_id = p.id
                                    WHERE EXISTS (
                                        SELECT 1 FROM transactions t
                                        WHERE t.participant_id = p.id AND t.status = 'PAID' LIMIT 1
                                    )
                                    AND (
                                        p.name LIKE ?
                                        OR p.bib  LIKE ?
                                        OR p.email LIKE ?
                                    )
                                    GROUP BY p.id, p.bib, p.name, e.name, ec.name
                                    ORDER BY p.bib ASC
                                    LIMIT 50",
                                    ["%{$search}%", "%{$search}%", "%{$search}%"]
                                );
                                return collect($rows)->mapWithKeys(function ($row) {
                                    $tagInfo = $row->active_tag_count > 0
                                        ? " [{$row->active_tag_count} tag]"
                                        : ' [belum ada tag]';

                                    $label = sprintf(
                                        '[%s] %s — %s (%s)%s',
                                        $row->bib ?? '-',
                                        $row->name,
                                        $row->event_name,
                                        $row->category_name,
                                        $tagInfo
                                    );

                                    return [$row->id => $label];
                                })->all();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                // Dipanggil saat form dibuka untuk edit — ambil label dari ID
                                $row = DB::selectOne(
                                    "SELECT p.id, p.bib, p.name, e.name AS event_name, ec.name AS category_name
                                     FROM participants p
                                     JOIN events e ON e.id = p.event_id
                                     JOIN event_categories ec ON ec.id = p.event_category_id
                                     WHERE p.id = ? LIMIT 1",
                                    [$value]
                                );

                                if (!$row) return null;

                                return sprintf(
                                    '[%s] %s — %s (%s)',
                                    $row->bib ?? '-',
                                    $row->name,
                                    $row->event_name,
                                    $row->category_name,
                                );
                            })
                            ->required()
                            ->live()
                            ->helperText('Ketik nama, BIB, atau email. Hanya peserta yang sudah konfirmasi pembayaran.')
                            ->afterStateUpdated(fn ($state, callable $set) => $set('_existing_tags', null)),

                        // Tag aktif milik participant yang dipilih
                        Placeholder::make('_existing_tags')
                            ->label('Tag RFID Aktif Saat Ini')
                            ->content(function ($get) {
                                $participantId = $get('participant_id');
                                if (!$participantId) {
                                    return new HtmlString(
                                        '<span class="text-sm text-gray-400">— Pilih participant dulu —</span>'
                                    );
                                }

                                // Raw query — tidak perlu model
                                $tags = DB::select(
                                    'SELECT rfid_tag, assigned_at, notes
                                     FROM participant_rfid_mappings
                                     WHERE participant_id = ? AND is_active = 1
                                     ORDER BY assigned_at DESC',
                                    [$participantId]
                                );

                                if (empty($tags)) {
                                    return new HtmlString(
                                        '<span class="text-sm text-gray-400">Belum ada tag aktif</span>'
                                    );
                                }

                                $badges = collect($tags)->map(function ($tag) {
                                    $tooltip = $tag->notes
                                        ? " title=\"{$tag->notes}\""
                                        : '';
                                    return "<span{$tooltip} class='inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mr-1 mb-1'>"
                                        . e($tag->rfid_tag)
                                        . '</span>';
                                })->join('');

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
