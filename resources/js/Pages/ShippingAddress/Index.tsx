import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {Head} from '@inertiajs/react';
import {Address, Country, PageProps} from "@/types";
import AddressItem from "@/Pages/ShippingAddress/Partials/AddressItem";
import {PlusCircleIcon} from "@heroicons/react/24/solid";
import AddressFormModal from "@/Pages/ShippingAddress/Partials/AddressFormModal";
import {useState} from "react";

export default function Index(
  {addresses, countries}: PageProps<{ addresses: Address[], countries: Country[] }>) {
  const [showNewAddressModal, setShowNewAddressModal] = useState(false);
  const [editAddress, setEditAddress] = useState<Address | null>(null);

  const onEditClick = (address: Address) => {
    setShowNewAddressModal(true);
    setEditAddress(address)
    console.log(address)
  }

  return (
    <AuthenticatedLayout
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          My Addresses
        </h2>
      }
    >
      <Head title="Profile"/>

      <div className="py-8">
        <div className="mx-auto max-w-7xl gap-4 p-4">
          <div className="flex flex-wrap gap-4 bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
            <button onClick={() => setShowNewAddressModal(true)}
                    className="w-full md:w-[280px] min-h-48 border-2 dark:border-gray-700 border-dashed rounded-xl flex flex-col gap-4 items-center justify-center cursor-pointer hover:border-gray-300 hover:dark:border-gray-600 active:dark:border-gray-600">
              <PlusCircleIcon className={"w-16 text-gray-500 dark:text-gray-300"}/>
              <span className={"text-lg"}>Add New Address</span>
            </button>
            {addresses.map(address => <AddressItem key={address.id} address={address}
                                                   onEdit={onEditClick}/>)}
          </div>
        </div>
      </div>

      <AddressFormModal countries={countries}
                        address={editAddress}
                        show={showNewAddressModal}
                        onHide={() => setShowNewAddressModal(false)}/>
    </AuthenticatedLayout>
  );
}
