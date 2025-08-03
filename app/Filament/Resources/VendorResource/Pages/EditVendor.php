<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\VendorResource;
use App\Mail\VendorStatusChanged;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;

class EditVendor extends EditRecord
{
    protected static string $resource = VendorResource::class;

    /**
     * Suprascriem metoda pentru a detecta schimbarea statusului și a trimite email.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $oldStatus = $record->status;

        // Updatează datele
        $record->update($data);

        // Trimite email dacă statusul s-a schimbat
        if ($oldStatus !== $record->status && $record->user && $record->user->email) {
            Mail::to($record->user->email)->send(new VendorStatusChanged($record));
        }

        return $record;
    }
}
