import React from 'react';
import {Address} from "@/types";
import {router} from "@inertiajs/react";

function AddressItem(
  {
    address, onEdit, className = '', readonly = false, defaultBadge = true
  }: {
    address: Address, onEdit?: Function, className?: string, readonly?: boolean, defaultBadge?: boolean
  }) {
  const makeDefault = () => {
    if (!confirm('Are you sure you want to make this address your default shipping address?')) {
      return;
    }
    router.put(route('shippingAddress.makeDefault', address.id), {}, {
      preserveScroll: true,
      preserveState: true,
    });
  }

  const deleteAddress = () => {
    if (address.default) {
      alert('You cannot delete your default address. Please make another address default before deleting this one.');
      return;
    }
    if (!confirm('Are you sure you want to delete this address?')) {
      return;
    }
    router.delete(route('shippingAddress.destroy', address.id), {
      preserveScroll: true,
      preserveState: true,
    });
  }

  const showEditAddress = () => {
    if (!onEdit) {
      return;
    }
    onEdit(address);
  }

  return (
    <div
      className={'text-sm p-4 border-2 dark:border-gray-700 rounded-xl w-full md:w-[280px] min-h-48 relative overflow-hidden pr-16 ' +
          (address.default ? 'border-primary dark:border-primary' : '') + className}>
      <h3 className="font-black">{address.full_name}</h3>
      <div>{address.address1} <br/>
        {address.address2 && <>
          {address.address2} <br/>
        </>}
        {address.city}, {address.state} {address.zipcode} <br/>
        {address.country.name} <br/>
        Phone Number: {address.phone} <br/>
        {address.delivery_instructions && <>
          <hr className="my-2"/>
          <em>{address.delivery_instructions}</em>
        </>}
      </div>
      {address.default && defaultBadge && <div
        className="absolute transform rotate-45 bold bg-primary py-1 px-3 text-white top-[20px] -right-[60px] w-48 text-center">
        Default
      </div>}
      {!readonly && <div className="flex gap-2 mt-4 whitespace-nowrap">
        {!address.default && <>
          <button onClick={ev => makeDefault()}
                  className="text-primary hover:underline">Make Default
          </button>
          |
        </>}
        <button onClick={ev => showEditAddress()}
                className="text-primary hover:underline">Edit
        </button>
        |
        <button onClick={ev => deleteAddress()}
                className="text-error hover:underline">Delete</button>
      </div>}
    </div>
  );
}

export default AddressItem;
