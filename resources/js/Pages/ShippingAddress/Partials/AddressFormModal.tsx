import React, {FormEventHandler, useEffect, useMemo} from 'react';
import SecondaryButton from "@/Components/Core/SecondaryButton";
import PrimaryButton from "@/Components/Core/PrimaryButton";
import Modal from "@/Components/Core/Modal";
import {useForm} from "@inertiajs/react";
import InputGroup from "@/Components/Core/InputGroup";
import InputLabel from "@/Components/Core/InputLabel";
import InputError from "@/Components/Core/InputError";
import {Address, Country} from "@/types";

function AddressFormModal(
  {
    countries, show, onHide, address
  }: {
    countries: Country[], show: boolean, onHide: Function, address: Address | null
  }) {
  const form = useForm({
    id: 0,
    country_code: '',
    full_name: '',
    phone: '',
    city: '',
    type: 'shipping',
    zipcode: '',
    address1: '',
    address2: '',
    state: '',
    default: false,
    delivery_instructions: ''
  });

  const selectedCountry = useMemo(() => {
    return countries.find(country => country.code === form.data.country_code);
  }, [form.data.country_code]);

  const onSubmit: FormEventHandler = (ev) => {
    ev.preventDefault();
    if (form.data.id) {
      form.put(route('shippingAddress.update', form.data.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          form.reset();
          closeModal();
        }
      })
    } else {
      form.post(route('shippingAddress.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          form.reset();
          closeModal();
        }
      })
    }
  }

  const closeModal = () => {
    onHide();
  };

  useEffect(() => {
    // Update form data when address prop changes
    if (address) {
      form.setData({
        id: address.id,
        country_code: address.country_code,
        full_name: address.full_name,
        phone: address.phone,
        city: address.city,
        zipcode: address.zipcode,
        address1: address.address1,
        address2: address.address2 || '',
        state: address.state,
        default: address.default,
        delivery_instructions: address.delivery_instructions || '',
        type: address.type
      })
    }
  }, [address]);

  return (
    <Modal show={show} onClose={closeModal}>
      <form onSubmit={onSubmit} className="p-4 md:p-8">
        <h2 className="text-xl font-medium text-gray-900 dark:text-gray-100 mb-4">
          {form.data.id ? 'Update Address' : 'Add a new address'}
        </h2>

        <div className="mb-3">
          <InputLabel htmlFor="country_code">
            Country <span className="text-error">*</span>
          </InputLabel>

          <select
            id="country_code"
            name="country_code"
            value={form.data.country_code}
            className={"select select-bordered w-full mt-1 " + (form.errors.country_code ? 'select-error' : '')}
            onChange={(e) => form.setData('country_code', e.target.value)}
          >
            <option value="">Select a country</option>
            {countries.map(country => <option key={country.code} value={country.code}>{country.name}</option>)}
          </select>

          <InputError message={form.errors.country_code} className="mt-2"/>
        </div>
        <div className="flex flex-col sm:flex-row gap-4 mb-3">
          <InputGroup form={form} label='Full Name' field='full_name' className="w-full" required/>
          <InputGroup form={form} label='Phone' field='phone' className="w-full" required/>
        </div>
        <div className="flex flex-col sm:flex-row gap-4 mb-3">
          <InputGroup form={form} label='Address line 1' field='address1' className="w-full" required/>
          <InputGroup form={form} label='Address line 2' field='address2' className="w-full"/>
        </div>
        <div className="flex flex-col sm:flex-row gap-4 mb-3">
          <InputGroup form={form} label='City' field='city' required/>
          {selectedCountry?.states && Object.keys(selectedCountry.states).length > 0 && (
            <div>
              <InputLabel htmlFor="state">
                State <span className="text-error">*</span>
              </InputLabel>

              <select
                id="state"
                name="state"
                value={form.data.state}
                className={"select select-bordered w-full mt-1" + (form.errors.state ? ' select-error' : '')}
                onChange={(e) => form.setData('state', e.target.value)}
              >
                <option value="">Select a state</option>
                {Object.entries(selectedCountry.states)
                  .sort((a, b) => a[1].localeCompare(b[1]))
                  .map(([code, state]) => <option key={code} value={code}>{state}</option>)}
              </select>

              <InputError message={form.errors.state} className="mt-2"/>
            </div>
          )}
          {!(selectedCountry?.states && Object.keys(selectedCountry.states).length > 0) && (
            <InputGroup form={form} label='State' field='state'/>
          )}
          <InputGroup form={form} label='Zipcode' field='zipcode' required/>
        </div>
        <InputGroup type="checkbox" form={form} label='Default Shipping Address' field='default' className="mb-3"/>
        <InputGroup type="textarea"
                    form={form}
                    label='Delivery Instructions'
                    placeholder="Enter your delivery instructions here"
                    field='delivery_instructions'/>

        <div className="mt-6 flex justify-end">
          <SecondaryButton onClick={closeModal}>
            Cancel
          </SecondaryButton>

          <PrimaryButton className="ms-3" disabled={form.processing}>
            Confirm
          </PrimaryButton>
        </div>
      </form>
    </Modal>
  );
}

export default AddressFormModal;
