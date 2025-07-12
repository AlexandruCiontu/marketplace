<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Generate and display an invoice for the order in admin panel.
     */
    public function invoice(Order $order)
    {
        // Check user permissions - should be authenticated admin or vendor that owns the order
        if (!auth()->check() ||
            (!auth()->user()->isAdmin() && $order->vendor_user_id !== auth()->id())) {
            abort(403);
        }

        // Load relationships
        $order->load(['user', 'orderItems.product', 'shippingAddress.country']);
        // We don't need to eager load variationOptions as it's now an accessor
        
        // Calculate subtotal
        $subtotal = 0;
        foreach ($order->orderItems as $item) {
            $subtotal += $item->price * $item->quantity;
        }
        $order->subtotal = $subtotal;

        // Generate invoice and return view
        return view('admin.orders.invoice', compact('order'));
    }
}
