import {PageProps, GroupedCartItems, Address} from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {Head, Link, router} from "@inertiajs/react";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import PrimaryButton from "@/Components/Core/PrimaryButton";
import {CreditCardIcon} from "@heroicons/react/24/outline";
import CartItem from "@/Components/App/CartItem";
import AddressItem from "@/Pages/ShippingAddress/Partials/AddressItem";
import SelectAddress from "@/Components/App/SelectAddress";
import rates from '@/data/rates.json';
import { calculateVatAndGross } from '@/utils/vat';
import { useVatCountry } from '@/hooks/useVatCountry';

function Index(
  {
    csrf_token,
    cartItems,
    totalQuantity,
    totalPrice,
    totalGross,
    shippingAddress,
    addresses
  }: PageProps<{
    cartItems: Record<number, GroupedCartItems>,
    shippingAddress: Address,
    addresses: Address[]
  }>) {
  const { countryCode, updateCountry } = useVatCountry();

  const countryName = rates.rates?.[countryCode]?.country ?? countryCode
  const countryName = rates.rates?.[countryCode]?.country ?? countryCode

  const fallbackGross = Object.values(cartItems).reduce((acc, group) => {
    return acc + group.items.reduce((a, item) => {
      const gross = item.price_with_vat ?? calculateVatAndGross(item.price, item.vat_rate_type ?? 'standard', countryCode).gross
      return a + gross * item.quantity
    }, 0)
  }, 0)
  const fallbackPrice = Object.values(cartItems).reduce((acc, group) => {
    return acc + group.items.reduce((a, item) => a + item.price * item.quantity, 0)
  }, 0)

  const displayedTotalGross = totalGross ?? fallbackGross
  const displayedTotalPrice = totalPrice ?? fallbackPrice

  const onAddressChange = (address: Address) => {
    router.put(route('cart.shippingAddress', address.id), {}, {
      preserveScroll: true,
      preserveState: true,
    });
  }

  return (
    <AuthenticatedLayout>
      <Head title="Your Cart"/>

      <div className="container mx-auto p-4 md:p-8 flex flex-col lg:flex-row gap-4">
        <div className="card flex-1 bg-white dark:bg-gray-800 order-2 lg:order-1">
          <div className="card-body">
            <h2 className="text-lg font-bold">Shopping Cart</h2>

            <div className="my-4">
              {Object.keys(cartItems).length === 0 && (
                <div className="py-2 text-gray-500 text-center">
                  You don't have any items yet.
                </div>
              )}
              {Object.values(cartItems).map(cartItem => (
                <div key={cartItem.user.id}>
                  <div className={"flex flex-col sm:flex-row items-center justify-between pb-4 border-b border-gray-300 mb-4"}>
                    <Link href={route('vendor.profile', cartItem.user.name)} className={"underline"}>
                      {cartItem.user.name}
                    </Link>
                    <form action={route('cart.checkout')} method="post">
                      <input type="hidden" name="_token" value={csrf_token}/>
                      <input type="hidden" name="vendor_id" value={cartItem.user.id}/>
                      <button className="btn btn-sm btn-ghost">
                        <CreditCardIcon className={"size-6"}/>
                        Pay Only for this seller
                      </button>
                    </form>
                  </div>
                  {cartItem.items.map(item => (
                    <CartItem item={item} key={item.id}/>
                  ))}
                </div>
              ))}
            </div>
          </div>
        </div>

        <div className="lg:min-w-[260px] order-1 lg:order-2">
          <div className="card bg-white dark:bg-gray-800 mb-4">
            <div className="card-body">
              {shippingAddress && (
                <>
                  <h2 className="text-lg font-bold border-b pb-2 mb-2">
                    Shipping Address
                  </h2>
                  <AddressItem address={shippingAddress}
                               readonly={true}
                               defaultBadge={false}
                               className="w-auto h-auto border-none !p-0 pr-0"/>
                </>
              )}
              {!shippingAddress && (
                <div className="text-gray-500 text-center">
                  No shipping address selected. <br/>
                </div>
              )}
              <SelectAddress addresses={addresses}
                             selectedAddress={shippingAddress}
                             onChange={onAddressChange}
                             buttonLabel="Change Address"/>

              <div className="mt-2 text-sm text-gray-500">VAT Country: {countryName}</div>

              {/* 🌍 Selector țară pentru TVA */}
              <div className="mt-6">
                <label htmlFor="vat_country" className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  VAT Country
                </label>
                <select
                  id="vat_country"
                  value={countryCode}
                  onChange={(e) => updateCountry(e.target.value)}
                  className="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                >
                  {[
                    'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE',
                    'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT',
                    'RO', 'SK', 'SI', 'ES', 'SE'
                  ].map(code => (
                    <option key={code} value={code}>{code}</option>
                  ))}
                </select>
              </div>
            </div>
          </div>

          <div className="card bg-white dark:bg-gray-800">
            <div className="card-body gap-1">
              <div className="flex justify-between">
                <span>Subtotal ({totalQuantity})</span>
                <CurrencyFormatter amount={displayedTotalPrice}/>
              </div>
              <div className="flex justify-between">
                <span>Shipping</span>
                <span>N/A</span>
              </div>
              <div className="flex justify-between">
                <span>VAT</span>
                <CurrencyFormatter amount={displayedTotalGross - displayedTotalPrice}/>
              </div>
              <div className="flex justify-between font-bold text-xl">
                <span>Total</span>
                <CurrencyFormatter amount={displayedTotalGross}/>
              </div>
              <form action={route('cart.checkout')} method="post">
                <input type="hidden" name="_token" value={csrf_token}/>
                <PrimaryButton className="rounded-full w-full mt-4" disabled={!shippingAddress}>
                  <CreditCardIcon className={"size-6"}/>
                  Proceed to checkout
                </PrimaryButton>
              </form>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}

export default Index;
