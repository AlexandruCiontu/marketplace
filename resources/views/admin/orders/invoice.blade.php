<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .invoice-header img {
            max-height: 70px;
        }
        h1, h2, h3, h4 {
            margin-top: 0;
            color: #1a202c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .address-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .address-block {
            flex: 1;
            max-width: 45%;
        }
        .totals-table {
            width: 40%;
            margin-left: auto;
        }
        .totals-table th, .totals-table td {
            padding: 5px;
        }
        .totals-table th {
            text-align: left;
            font-weight: normal;
        }
        .totals-table td {
            text-align: right;
        }
        .grand-total {
            font-weight: bold;
            font-size: 1.2em;
            border-top: 2px solid #eee;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 0.85em;
            color: #666;
        }
        @media print {
            body {
                padding: 0;
                background-color: white;
            }
            .invoice-container {
                box-shadow: none;
                border: none;
                padding: 0;
            }
            .print-button {
                display: none;
            }
        }
    </style>

    <script>
        window.onload = () => {
            window.print()
        }
    </script>
</head>
<body>
    <div class="invoice-container">
        <div class="print-button" style="text-align: right; margin-bottom: 20px;">
            <button style="padding: 8px 16px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Invoice</button>
        </div>

        <div class="invoice-header">
            <div>
                <h1>INVOICE</h1>
                <p>{{ config('app.name') }}</p>
            </div>
            <div>
                @if(isset($logo))
                    <img src="{{ $logo }}" alt="Logo">
                @endif
                <h2 style="margin-top: 10px;">Invoice #{{ $order->id }}</h2>
                <p>Date: {{ $order->created_at->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="address-details">
            <div class="address-block">
                <h3>Billed To:</h3>
                <p>
                    {{ $order->user->name }}<br>
                    {{ $order->user->email }}
                </p>
            </div>

            @if($order->shippingAddress)
            <div class="address-block">
                <h3>Shipped To:</h3>
                <p>
                    {{ $order->shippingAddress->full_name }}<br>
                    {{ $order->shippingAddress->address1 }}<br>
                    @if($order->shippingAddress->address2)
                        {{ $order->shippingAddress->address2 }}<br>
                    @endif
                    {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->zipcode }}<br>
                    {{ $order->shippingAddress->country->name }}<br>
                    {{ $order->shippingAddress->phone }}
                </p>
            </div>
            @endif
        </div>

        <h3>Order Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Variations</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $item->product ? $item->product->title : 'Product not available' }}</td>
                    <td>
                        @if($item->variationOptions && $item->variationOptions->count() > 0)
                            @foreach($item->variationOptions as $option)
                                {{ $option->variationType->name }}: {{ $option->name }}@if(!$loop->last), @endif
                            @endforeach
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->price, 2) }} {{ config('app.currency') }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price * $item->quantity, 2) }} {{ config('app.currency') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <th>Subtotal:</th>
                <td>{{ number_format($order->subtotal, 2) }} {{ config('app.currency') }}</td>
            </tr>
            @if($order->tax_amount > 0)
            <tr>
                <th>Tax:</th>
                <td>{{ number_format($order->tax_amount, 2) }} {{ config('app.currency') }}</td>
            </tr>
            @endif
            @if($order->shipping_amount > 0)
            <tr>
                <th>Shipping:</th>
                <td>{{ number_format($order->shipping_amount, 2) }} {{ config('app.currency') }}</td>
            </tr>
            @endif
            @if($order->discount_amount > 0)
            <tr>
                <th>Discount:</th>
                <td>-{{ number_format($order->discount_amount, 2) }} {{ config('app.currency') }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <th>Total:</th>
                <td>{{ number_format($order->total_price, 2) }} {{ config('app.currency') }}</td>
            </tr>
        </table>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>For any questions regarding this invoice, please contact {{ config('app.name') }} customer service.</p>
        </div>
    </div>
</body>
</html>
