import React, {useState} from 'react';
import {Address} from "@/types";
import AddressItem from "@/Pages/ShippingAddress/Partials/AddressItem";
import Modal from "@/Components/Core/Modal";
import SecondaryButton from "@/Components/Core/SecondaryButton";
import PrimaryButton from "@/Components/Core/PrimaryButton";
import {Link} from "@inertiajs/react";

function SelectAddress(
  {
    addresses, selectedAddress = null, onChange, buttonLabel = 'Select a shipping address'
  }: {
    addresses: Address[], selectedAddress?: Address | null, onChange?: Function, buttonLabel?: string
  }) {
  const [show, setShow] = useState(false);
  const [selected, setSelected] = useState<Address | null>(selectedAddress);

  const showModal = () => {
    setShow(true);
  }

  const closeModal = () => {
    setShow(false);
  }

  const selectAddress = (address: Address) => {
    setSelected(address);
  }

  const onSubmit = () => {
    if (onChange) {
      onChange(selected);
    }
    closeModal();
  }

  return (
    <div>
      <button onClick={showModal} className="link link-primary">{buttonLabel}</button>
      <Modal show={show} onClose={closeModal} maxWidth="3xl">
        <div className="p-4 md:p-8">
          <h2 className="text-xl font-medium text-gray-900 dark:text-gray-100 mb-4">
            Select a shipping address
          </h2>
          <div className="flex flex-wrap gap-2">
            {!addresses.length && (
              <div className="text-center py-16 px-8 flex-1 text-gray-500">
                You don't have any addresses yet.
              </div>
            )}
            {addresses.map(address => (
              <div key={address.id} className="flex items-center gap-4">
                <div key={address.id} className="form-control">
                  <label className="label cursor-pointer gap-4">
                    <input onChange={ev => selectAddress(address)}
                           type="radio"
                           name="selected_address"
                           checked={selected?.id === address.id}
                           className="radio checked:bg-primary"/>

                    <AddressItem address={address}
                                 readonly={true}
                                 className={'w-[260px] hover:border-primary ' + (selected?.id == address.id ? 'border-primary dark:border-primary' : '')}/>
                  </label>
                </div>
              </div>
            ))}
          </div>
          <div className="mt-4">
            <Link href={route('shippingAddress.index')} className="link">Manage addresses</Link>
          </div>

          <div className="mt-6 flex justify-end">
            <SecondaryButton onClick={closeModal}>
              Cancel
            </SecondaryButton>

            <PrimaryButton onClick={onSubmit} className="ms-3">
              Select
            </PrimaryButton>
          </div>
        </div>
      </Modal>
    </div>
  );
}

export default SelectAddress;
