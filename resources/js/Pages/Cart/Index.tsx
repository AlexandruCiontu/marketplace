import {PageProps, GroupedCartItems, Address} from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {Head, Link, router} from "@inertiajs/react";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import PrimaryButton from "@/Components/Core/PrimaryButton";
import {CreditCardIcon} from "@heroicons/react/24/outline";
import CartItem from "@/Components/App/CartItem";
import AddressItem from "@/Pages/ShippingAddress/Partials/AddressItem";
import SelectAddress from "@/Components/App/SelectAddress";

function Index(
  {
    csrf_token,
    cartItems,
    totalQuantity,
    totalPrice,
    shippingAddress,
    addresses
  }: PageProps<{
    cartItems: Record<number, GroupedCartItems>,
    shippingAddress: Address,
    addresses: Address[]
  }>) {


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
            <h2 className="text-lg font-bold">
              Shopping Cart
            </h2>

            <div className="my-4">
              {Object.keys(cartItems).length === 0 && (
                <div className="py-2 text-gray-500 text-center">
                  You don't have any items yet.
                </div>
              )}
              {Object.values(cartItems).map(cartItem => (
                <div key={cartItem.user.id}>
                  <div
                    className={"flex flex-col sm:flex-row items-center justify-between pb-4 border-b border-gray-300 mb-4"}>
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
            </div>
          </div>
          <div className="card bg-white dark:bg-gray-800">
            <div className="card-body gap-1">
              <div className="flex justify-between">
                <span>Items ({totalQuantity})</span>
                <CurrencyFormatter amount={totalPrice}/>
              </div>
              <div className="flex justify-between">
                <span>Shipping</span>
                <span>N/A</span>
              </div>
              <div className="flex justify-between">
                <span>Tax</span>
                <span>N/A</span>
              </div>
              <div className="flex justify-between font-bold text-xl">
                <span>Order Total</span>
                <CurrencyFormatter amount={totalPrice}/>
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
