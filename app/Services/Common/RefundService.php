<?php

namespace App\Services\Common;

use App\Models\Order;
use App\Models\OssTransaction;
use App\Services\VendorCountry\InvoiceServiceFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

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
                'online_payment_commission' => -$originalOrder->online_payment_commission,
                'website_commission' => -$originalOrder->website_commission,
                'vendor_subtotal' => -$originalOrder->vendor_subtotal,
                'included_in_oss' => $originalOrder->included_in_oss,
            ]);
            $refundOrder->save();

            // 2. Copy order items with negative quantities/prices
            foreach ($originalOrder->orderItems as $item) {
                $refundItem = $item->replicate();
                $refundItem->order_id = $refundOrder->id;
                $refundItem->quantity = -$item->quantity;
                $refundItem->save();
            }

            // 3. Interact with Stripe for the actual refund
            if ($originalOrder->payment_intent) {
                $stripe = new StripeClient(config('app.stripe_secret_key'));
                $stripe->refunds->create([
                    'payment_intent' => $originalOrder->payment_intent,
                    'amount' => (int) round($originalOrder->total_price * 100),
                ]);
            }

            // 4. Generate the storno invoice
            $invoiceService = $this->invoiceServiceFactory->make($originalOrder->vendor);
            $invoiceService->generateStorno($originalOrder, $refundOrder);

            // 5. Ledger adjustment
            if ($originalOrder->included_in_oss) {
                OssTransaction::create([
                    'vendor_id' => $originalOrder->vendor_user_id,
                    'order_id' => $refundOrder->id,
                    'client_country_code' => $originalOrder->vat_country_code,
                    'vat_rate' => $originalOrder->net_total != 0 ? $originalOrder->vat_total / $originalOrder->net_total * 100 : 0,
                    'net_amount' => -$originalOrder->net_total,
                    'vat_amount' => -$originalOrder->vat_total,
                    'gross_amount' => -$originalOrder->total_price,
                ]);
            }

            DB::commit();

            return $refundOrder;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create refund for order {$originalOrder->id}: ".$e->getMessage());
            throw $e;
        }
    }
}
