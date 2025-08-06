<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Http\Resources\ShippingAddressResource;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\CartService;
use App\Services\TransactionClassifierService;
use App\Services\VatRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CartService $cartService)
    {
        [$user, $defaultAddress] = $this->userShippingAddress();

        $totals = $cartService->getTotals();

        return Inertia::render('Cart/Index', [
            'cartItems' => $cartService->getCartItemsGrouped(),
            'addresses' => $user ? ShippingAddressResource::collection($user->shippingAddresses)->collection->toArray() : [],
            'shippingAddress' => $defaultAddress ? new ShippingAddressResource($defaultAddress) : null,
            'countryCode' => session('country_code', 'RO'),
            'gross_total' => $totals['gross_total'],
            'vat_total' => $totals['vat_total'],
            'net_total' => $totals['net_total'],
        ]);
    }

    public function setVatCountry(Request $request)
    {
        $code = $request->input('vat_country');

        $euCountries = [
            'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE',
            'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT',
            'RO', 'SK', 'SI', 'ES', 'SE',
        ];

        if (! in_array($code, $euCountries)) {
            $code = 'RO';
        }

        session(['country_code' => $code]);

        return back();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product, CartService $cartService)
    {
        $request->mergeIfMissing([
            'quantity' => 1,
        ]);

        $data = $request->validate([
            'option_ids' => ['nullable', 'array'],
            'quantity' => [
                'required', 'integer', 'min:1',
            ],
        ]);

        $productTotalQuantity = $product->getTotalQuantity($data['option_ids']);
        $cartQuantity = $cartService->getQuantity($product, $data['option_ids']);

        if ($cartQuantity + $data['quantity'] > $productTotalQuantity) {
            $message = match ($productTotalQuantity - $cartQuantity) {
                0 => 'The Product is out of stock',
                1 => 'There is only 1 item left in stock',
                default => 'There are only '.($productTotalQuantity - $cartQuantity).' items left in stock'
            };

            return back()->with('errorToast', $message);
        }

        $cartService->addItemToCart(
            $product,
            $data['quantity'],
            $data['option_ids'] ?: []
        );

        return back()->with('successToast', 'Product added to cart successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product, CartService $cartService)
    {
        $request->validate([
            'quantity' => [
                'integer', 'min:1', function ($attribute, $value, $fail) use ($product, $request) {
                    $optionIds = $request->input('option_ids') ?: [];
                    $productTotalQuantity = $product->getTotalQuantity($optionIds);

                    if ($value > $productTotalQuantity) {
                        $fail("There are only {$productTotalQuantity} items left in stock");
                    }
                },
            ],
        ]);

        $optionIds = $request->input('option_ids') ?: []; // Get the option IDs (if applicable)
        $quantity = $request->input('quantity'); // Get the new quantity

        $cartService->updateItemQuantity($product->id, $quantity, $optionIds);

        return back()->with('successToast', 'Quantity was updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product, CartService $cartService)
    {
        $optionIds = $request->input('option_ids');

        $cartService->removeItemFromCart($product->id, $optionIds);

        return back()->with('successToast', 'Product was removed from cart.');
    }

    public function checkout(
        Request $request,
        CartService $cartService,
        TransactionClassifierService $classifier,
        VatRateService $vatRateService
    ) {
        \Stripe\Stripe::setApiKey(config('app.stripe_secret_key'));

        $vendorId = $request->input('vendor_id');

        $allCartItems = $cartService->getCartItemsGrouped();

        [$authUser, $defaultAddress] = $this->userShippingAddress();

        DB::beginTransaction();
        try {
            $checkoutCartItems = $allCartItems;
            if ($vendorId) {
                $checkoutCartItems = [$allCartItems[$vendorId]];
            }
            $orders = [];
            $lineItems = [];
            $clientCountryCode = $defaultAddress->country_code;

            foreach ($checkoutCartItems as $item) {
                $vendorUser = $item['user'];
                $cartItems = $item['items'];
                /** @var Vendor $vendor */
                $vendor = Vendor::find($vendorUser['id']);

                $transactionType = $classifier->classify($vendor, $authUser);

                $orderNet = 0;
                $orderVat = 0;
                $orderGross = 0;
                $order = Order::create([
                    'stripe_session_id' => null,
                    'user_id' => $authUser->id ?? auth()->id(),
                    'vendor_user_id' => $vendorUser['id'],
                    'total_price' => 0,
                    'net_total' => 0,
                    'vat_total' => 0,
                    'vat_country_code' => $clientCountryCode,
                    'transaction_type' => $transactionType,
                    'status' => OrderStatusEnum::Draft->value,
                ]);
                $tmpAddressData = $defaultAddress->toArray();
                $tmpAddressData['addressable_id'] = $order->id;
                $tmpAddressData['addressable_type'] = Order::class;
                unset($tmpAddressData['id']);
                $order->shippingAddress()->create($tmpAddressData);
                $orders[] = $order;

                foreach ($cartItems as $cartItem) {
                    $vatCountry = $transactionType === 'OSS' ? $clientCountryCode : $vendor->country_code;

                    $calc = $vatRateService->calculate($cartItem['price'], $cartItem['vat_rate_type'], $vatCountry);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem['product_id'],
                        'quantity' => $cartItem['quantity'],
                        'net_price' => $cartItem['price'],
                        'vat_rate' => $calc['rate'],
                        'vat_amount' => $calc['vat'],
                        'gross_price' => $calc['gross'],
                        'variation_type_option_ids' => $cartItem['option_ids'],
                    ]);

                    $orderNet += $cartItem['price'] * $cartItem['quantity'];
                    $orderGross += $calc['gross'] * $cartItem['quantity'];
                    $orderVat += $calc['vat'] * $cartItem['quantity'];

                    $description = collect($cartItem['options'])->map(function ($item) {
                        return "{$item['type']['name']}: {$item['name']}";
                    })->implode(', ');

                    $lineItem = [
                        'price_data' => [
                            'currency' => config('app.currency'),
                            'product_data' => [
                                'name' => $cartItem['title'],
                                'images' => [$cartItem['image']],
                            ],
                            // send net price and let Stripe compute VAT automatically
                            'unit_amount' => (int) round($cartItem['price'] * 100),
                            'tax_behavior' => 'exclusive',
                        ],
                        'quantity' => $cartItem['quantity'],
                    ];
                    if ($description) {
                        $lineItem['price_data']['product_data']['description'] = $description;
                    }
                    $lineItems[] = $lineItem;
                }

                $order->update([
                    'total_price' => $orderGross,
                    'net_total' => $orderNet,
                    'vat_total' => $orderVat,
                ]);
            }
            $customerId = $authUser->stripe_customer_id;
            $address = array_filter([
                'line1' => $defaultAddress->address1,
                'line2' => $defaultAddress->address2,
                'city' => $defaultAddress->city,
                'state' => $defaultAddress->state,
                'postal_code' => $defaultAddress->zipcode,
                'country' => $defaultAddress->country_code,
            ]);

            if (! $customerId) {
                $customer = \Stripe\Customer::create([
                    'email' => $authUser->email,
                    'name' => $authUser->name,
                    'address' => $address,
                    'shipping' => [
                        'name' => $authUser->name,
                        'address' => $address,
                    ],
                ]);
                $customerId = $customer->id;
                $authUser->stripe_customer_id = $customerId;
                $authUser->save();
            } else {
                \Stripe\Customer::update($customerId, [
                    'address' => $address,
                    'shipping' => [
                        'name' => $authUser->name,
                        'address' => $address,
                    ],
                ]);
            }

            $session = \Stripe\Checkout\Session::create([
                'customer' => $customerId,
                'customer_email' => $authUser->email,
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('stripe.success', []).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('stripe.failure', []),
                'customer_update' => ['name' => 'auto', 'address' => 'auto', 'shipping' => 'auto'],
                'tax_id_collection' => ['enabled' => true],
                'billing_address_collection' => 'required',
                'shipping_address_collection' => [
                    'allowed_countries' => [
                        'RO', 'HU', 'BG', 'DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'CZ',
                        'DK', 'EE', 'FI', 'GR', 'HR', 'IE', 'LT', 'LU', 'LV', 'MT',
                        'PL', 'PT', 'SE', 'SI', 'SK', 'AT', 'CY',
                    ],
                ],
                'automatic_tax' => ['enabled' => true],
            ]);

            foreach ($orders as $order) {
                $order->stripe_session_id = $session->id;
                $order->save();
            }

            DB::commit();

            return redirect($session->url);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();

            return back()->with('error', $e->getMessage() ?: 'Something went wrong');
        }
    }

    public function updateShippingAddress(Address $address)
    {
        if (! $address->belongs(auth()->user())) {
            abort(403, 'Unauthorized');
        }
        // Update the shipping address in session and set VAT country
        session()->put('shipping_address_id', $address->id);
        session()->put('country_code', $address->country_code);

        return back();
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function userShippingAddress(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [null, null];
        }
        // Get shipping address from session
        $shippingAddressId = session()->get('shipping_address_id');
        if ($shippingAddressId) {
            $defaultAddress = $user->shippingAddresses->find($shippingAddressId);
        } else {
            $defaultAddress = $user->shippingAddress;
        }

        return [$user, $defaultAddress];
    }
}
