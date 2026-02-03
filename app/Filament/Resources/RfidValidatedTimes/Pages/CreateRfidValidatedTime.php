<?php

namespace App\Filament\Resources\RfidValidatedTimes\Pages;

use App\Filament\Resources\RfidValidatedTimes\RfidValidatedTimeResource;
use App\Services\RfidTimingService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateRfidValidatedTime extends CreateRecord
{
    protected static string $resource = RfidValidatedTimeResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Use the service for proper time calculations
        $service = app(RfidTimingService::class);

        try {
            $validatedTime = $service->manualEntry(
                $data['participant_id'],
                $data['rfid_checkpoint_id'],
                $data['checkpoint_time'],
                Auth::user()?->id ?? 1,
                $data['validation_notes'] ?? 'Manual entry from admin panel'
            );

            Notification::make()
                ->title('Time entry created successfully')
                ->body('Elapsed time and positions have been calculated.')
                ->success()
                ->send();

            return $validatedTime;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error creating time entry')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
