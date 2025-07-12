<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function afterSave(): void
    {
        $order = $this->record;
        $changes = [];
        
        // Check if status was changed
        if ($order->wasChanged('status')) {
            $changes['status'] = $order->status;
        }
        
        // Check if tracking code was added or changed
        if ($order->wasChanged('tracking_code') && $order->tracking_code) {
            $changes['tracking_code'] = $order->tracking_code;
            
            // Update the timestamp when tracking code was added
            $order->tracking_code_added_at = Carbon::now();
            $order->saveQuietly(); // Save without triggering events again
        }
        
        // If there are changes that need notification
        if (!empty($changes)) {
            // Send email notification to the customer
            $order->user->notify(new OrderStatusUpdated($order, $changes));
            
            // Show success notification to the admin/vendor
            Notification::make()
                ->title('Customer notified')
                ->body('The customer has been notified of the order update.')
                ->success()
                ->send();
        }
    }
}
