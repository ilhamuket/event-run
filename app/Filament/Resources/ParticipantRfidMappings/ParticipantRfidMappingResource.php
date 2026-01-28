<?php

namespace App\Filament\Resources\ParticipantRfidMappings;

use App\Filament\Resources\ParticipantRfidMappings\Pages\CreateParticipantRfidMapping;
use App\Filament\Resources\ParticipantRfidMappings\Pages\EditParticipantRfidMapping;
use App\Filament\Resources\ParticipantRfidMappings\Pages\ListParticipantRfidMappings;
use App\Filament\Resources\ParticipantRfidMappings\Pages\ViewParticipantRfidMapping;
use App\Filament\Resources\ParticipantRfidMappings\Schemas\ParticipantRfidMappingForm;
use App\Filament\Resources\ParticipantRfidMappings\Schemas\ParticipantRfidMappingInfolist;
use App\Filament\Resources\ParticipantRfidMappings\Tables\ParticipantRfidMappingsTable;
use App\Models\ParticipantRfidMapping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ParticipantRfidMappingResource extends Resource
{
    protected static ?string $model = ParticipantRfidMapping::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'ParticipantRfidMapping';

    public static function form(Schema $schema): Schema
    {
        return ParticipantRfidMappingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ParticipantRfidMappingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParticipantRfidMappingsTable::configure($table);
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
            'index' => ListParticipantRfidMappings::route('/'),
            'create' => CreateParticipantRfidMapping::route('/create'),
            'view' => ViewParticipantRfidMapping::route('/{record}'),
            'edit' => EditParticipantRfidMapping::route('/{record}/edit'),
        ];
    }
}
