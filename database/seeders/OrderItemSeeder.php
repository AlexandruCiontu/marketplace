<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the order with ID 2 (from your screenshot)
        $order = Order::find(2);
        
        if (!$order) {
            $this->command->error('Order #2 not found!');
            return;
        }
        
        // Get some products to add to the order
        $products = Product::take(2)->get();
        
        if ($products->isEmpty()) {
            $this->command->error('No products found!');
            return;
        }
        
        // Delete existing order items for this order
        OrderItem::where('order_id', $order->id)->delete();
        
        // Create order items for each product
        foreach ($products as $index => $product) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $index + 1,
                'price' => $product->price,
                'variation_type_option_ids' => []
            ]);
        }
        
        $this->command->info('Order items created successfully for Order #' . $order->id);
    }
}
