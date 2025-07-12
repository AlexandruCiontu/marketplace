<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index()
    {
        $orders = auth()->user()->orders()
            ->with(['orderItems.product', 'shippingAddress'])
            ->where('status', '!=', OrderStatusEnum::Draft->value) // Exclude draft orders
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Show 10 orders per page

        return Inertia::render('Orders/Index', [
            'orders' => OrderResource::collection($orders),
        ]);
    }

    /**
     * Display the specified order details.
     */
    public function show(Order $order)
    {
        // Ensure the user can only view their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load([
            'orderItems.product',
            'shippingAddress.country'
        ]);

        return Inertia::render('Orders/Show', [
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * Generate and display an invoice for the order.
     */
    public function invoice(Order $order)
    {
        // Ensure the user can only view invoices for their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Load relationships
        $order->load([
            'user',
            'orderItems.product',
            'shippingAddress.country'
        ]);

        // Calculate subtotal
        $subtotal = 0;
        foreach ($order->orderItems as $item) {
            $subtotal += $item->price * $item->quantity;
        }
        $order->subtotal = $subtotal;

        // For the invoice view, we'll still use the raw model with the subtotal added
        // Since this goes to a Blade view, not an Inertia component
        return view('orders.invoice', compact('order'));
    }
}
