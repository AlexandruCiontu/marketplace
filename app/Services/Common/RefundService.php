<?php

namespace App\Services\Common;

use App\Models\Order;
use App\Services\VendorCountry\InvoiceServiceFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundService
{
    public function __construct(private InvoiceServiceFactory $invoiceServiceFactory)
    {
    }

    /**
     * Creates a refund and generates a storno invoice.
     *
     * @param Order $originalOrder
     * @return Order The new refund order.
     * @throws \Exception
     */
    public function createRefund(Order $originalOrder): Order
    {
        DB::beginTransaction();
        try {
            // 1. Create a new Order record with negative values
            $refundOrder = new Order();
            $refundOrder->fill([
                'user_id' => $originalOrder->user_id,
                'vendor_user_id' => $originalOrder->vendor_user_id,
                'total_price' => -$originalOrder->total_price,
                'net_total' => -$originalOrder->net_total,
                'vat_total' => -$originalOrder->vat_total,
                'status' => 'refunded',
                'refund_id' => $originalOrder->id,
                'transaction_type' => $originalOrder->transaction_type,
                'vat_country_code' => $originalOrder->vat_country_code,
            ]);
            $refundOrder->save();

            // 2. Copy order items with negative quantities/prices
            foreach ($originalOrder->orderItems as $item) {
                $refundItem = $item->replicate();
                $refundItem->order_id = $refundOrder->id;
                $refundItem->quantity = -$item->quantity;
                $refundItem->save();
            }

            // 3. Generate the storno invoice
            $invoiceService = $this->invoiceServiceFactory->make($originalOrder->vendor);
            $invoiceService->generateStorno($originalOrder, $refundOrder);

            // 4. Potentially interact with Stripe for the actual refund
            // (Out of scope for this plan, but would be needed in a real app)

            DB::commit();

            return $refundOrder;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create refund for order {$originalOrder->id}: ".$e->getMessage());
            throw $e;
        }
    }
}
