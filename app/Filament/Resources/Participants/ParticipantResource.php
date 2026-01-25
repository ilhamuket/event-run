<?php

namespace App\Filament\Resources\Participants;

use App\Filament\Resources\Participants\Pages\CreateParticipant;
use App\Filament\Resources\Participants\Pages\EditParticipant;
use App\Filament\Resources\Participants\Pages\ListParticipants;
use App\Filament\Resources\Participants\Pages\ViewParticipant;
use App\Filament\Resources\Participants\Schemas\ParticipantForm;
use App\Filament\Resources\Participants\Schemas\ParticipantInfolist;
use App\Filament\Resources\Participants\Tables\ParticipantsTable;
use App\Models\Participant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Tables;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Participant';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('event_id')
            ->relationship('event', 'name')
            ->required(),


            TextInput::make('bib')
            ->required()
            ->unique(ignoreRecord: true),


            TextInput::make('name')
            ->required(),


            Select::make('gender')
            ->options([
            'M' => 'Male',
            'F' => 'Female',
            ])
            ->required(),


            TextInput::make('category'),


            TextInput::make('city'),


            // 🔽 race fields (optional, diisi pas race)
            TimePicker::make('elapsed_time')
            ->seconds(false),


            TextInput::make('general_position')
            ->numeric(),


            TextInput::make('category_position')
            ->numeric(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ParticipantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('elapsed_time')
            ->columns([
            Tables\Columns\TextColumn::make('bib')->sortable(),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('gender'),
            Tables\Columns\TextColumn::make('elapsed_time')->sortable(),
            Tables\Columns\TextColumn::make('general_position')->sortable(),
            Tables\Columns\TextColumn::make('category_position')->sortable(),
            Tables\Columns\TextColumn::make('city'),
            ])
            ->filters([
            Tables\Filters\SelectFilter::make('event')
            ->relationship('event', 'name'),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParticipants::route('/'),
            'create' => CreateParticipant::route('/create'),
            'view' => ViewParticipant::route('/{record}'),
            'edit' => EditParticipant::route('/{record}/edit'),
        ];
    }
}
