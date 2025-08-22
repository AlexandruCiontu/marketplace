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
     * Override method to detect status changes and send email.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $oldStatus = $record->status;

        // Update the data
        $record->update($data);

        // Send email if status changed
        if ($oldStatus !== $data['status'] && $record->user && $record->user->email) {
            Mail::to($record->user->email)->send(new VendorStatusChanged($record));

            // If status is changed to 'rejected', remove vendor and role
            if ($data['status'] === 'rejected') {
                $record->user->removeRole('Vendor');
                $record->delete();
                // You can add a redirect or notification message here if desired
                return new \App\Models\Vendor(); // Return an empty model to avoid further errors
            }
        }

        return $record;
    }
}
