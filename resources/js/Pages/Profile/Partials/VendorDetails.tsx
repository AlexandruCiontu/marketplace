import PrimaryButton from '@/Components/Core/PrimaryButton';
import {useForm, usePage} from '@inertiajs/react';
import React, {FormEventHandler, useState} from 'react';
import SecondaryButton from "@/Components/Core/SecondaryButton";
import Modal from "@/Components/Core/Modal";
import InputLabel from "@/Components/Core/InputLabel";
import TextInput from "@/Components/Core/TextInput";
import InputError from "@/Components/Core/InputError";

  const fiscalCountries = [
    {name: 'Romania', code: 'RO'},
    {name: 'Hungary', code: 'HU'},
    {name: 'Bulgaria', code: 'BG'},
  ];

export default function VendorDetails(
  {className = '',}: { className?: string; }
) {
  const [successMessage, setSuccessMessage] = useState('');
  const user = usePage().props.auth.user;
  const token = usePage().props.csrf_token

  const {
    data,
    setData,
    errors,
    post,
    processing,
    recentlySuccessful,
  } = useForm({
    store_name: user.vendor?.store_name || user.name.toLowerCase().replace(/\s+/g, '-'),
    store_address: user.vendor?.store_address,
    country_code: user.vendor?.country_code || 'RO',
    phone: user.vendor?.phone || user.phone,
    cif: user.vendor?.cif || '',
    anaf_pfx: null,
    anaf_certificate_password: '',
    nav_user_id: user.vendor?.nav_user_id || '',
    nav_exchange_key: user.vendor?.nav_exchange_key || '',
  });

  const onStoreNameChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
    setData('store_name', ev.target.value.toLowerCase().replace(/\s+/g, '-'))
  }

  const becomeVendor: FormEventHandler = (ev) => {
    ev.preventDefault()

    post(route('vendor.store'), {
      preserveScroll: true,
      onSuccess: () => {
        closeModal()
        setSuccessMessage('You can now create and publish products.')
      },
      onError: (errors) => {

      },
    })
  }

  const handleSubmit: FormEventHandler = (ev) => {
    ev.preventDefault()

    post(route('vendor.store'), {
      preserveScroll: true,
      onSuccess: () => {
        const message = user.vendor ? 'Your details were updated.' : 'Your vendor request has been submitted and is pending approval.';
        setSuccessMessage(message);
      },
      onError: (errors) => {

      },
    })
  }

  return (
    <section className={className}>
      {recentlySuccessful && <div className="toast toast-top toast-end">
        <div className="alert alert-success">
          <span>{successMessage}</span>
        </div>
      </div>}

      <header>
        <h2 className="flex justify-between mb-8 text-lg font-medium text-gray-900 dark:text-gray-100">
          Vendor Details
        </h2>
      </header>

      {user.vendor && (
        <div>
            <p className="text-green-700 mb-4">
                You are already a vendor. Status: <strong>{user.vendor.status}</strong>
            </p>
            <form action={route('stripe.connect')}
                  method={'post'}
                  className={'my-8'}>
                <input type="hidden" name="_token" value={token}/>
              {user.stripe_account_active && (
                  <div className={'text-center text-gray-600 my-4 text-sm'}>
                      You are successfully connected to Stripe
                  </div>
              )}
                <button className="btn btn-primary w-full"
                        disabled={user.stripe_account_active}>
                    Connect to Stripe
                </button>
            </form>
        </div>
      )}

      {!user.vendor && (
          <form
              onSubmit={handleSubmit}
              className="bg-white rounded-lg shadow-md p-6 space-y-4"
              encType="multipart/form-data"
          >
              <h2 className="text-lg font-bold">Become a Vendor</h2>

              <div className="mb-4">
                  <InputLabel htmlFor="name" value="Store Name"/>
                  <TextInput id="name" className="mt-1 block w-full" value={data.store_name}
                             onChange={onStoreNameChange} required isFocused autoComplete="name"/>
                  <small className="text-gray-500 mt-1">
                      Tip: Choose a name different from your user name.
                  </small>
                  <InputError className="mt-2" message={errors.store_name}/>
              </div>

              <div className="mb-4">
                  <InputLabel htmlFor="name" value="Store Address"/>
                  <textarea className="textarea textarea-bordered w-full mt-1" value={data.store_address}
                            onChange={(e) => setData('store_address', e.target.value)}
                            placeholder="Enter Your Store Address"></textarea>
                  <InputError className="mt-2" message={errors.store_address}/>
              </div>

              <div className="mb-4">
                  <InputLabel htmlFor="country_code" value="Country"/>
                  <select id="country_code" name="country_code" value={data.country_code}
                          className="select select-bordered w-full mt-1"
                          onChange={(e) => setData('country_code', e.target.value)}
                          required>
                    {fiscalCountries.map(c => (
                        <option key={c.code} value={c.code}>
                          {c.name}
                        </option>
                    ))}
                  </select>
                  <InputError className="mt-2" message={errors.country_code}/>
              </div>

            {data.country_code === 'RO' && (
                <>
                    <div className="mb-4">
                        <InputLabel htmlFor="cif" value="CIF"/>
                        <TextInput id="cif" className="mt-1 block w-full" value={data.cif}
                                      onChange={(e) => setData('cif', e.target.value)}/>
                        <InputError className="mt-2" message={errors.cif}/>
                    </div>
                    <div className="mb-4">
                        <InputLabel htmlFor="anaf_pfx" value="Certificat e-Factura (.pfx)"/>
                        <input
                            type="file"
                            id="anaf_pfx"
                            className="file-input file-input-bordered w-full mt-1"
                            onChange={(e) => setData('anaf_pfx', e.target.files ? e.target.files[0] : null)}
                        />
                        <InputError className="mt-2" message={errors.anaf_pfx}/>
                    </div>
                    <div className="mb-4">
                        <InputLabel htmlFor="anaf_certificate_password" value="Parola Certificat"/>
                        <TextInput id="anaf_certificate_password" type="password" className="mt-1 block w-full"
                                      value={data.anaf_certificate_password}
                                      onChange={(e) => setData('anaf_certificate_password', e.target.value)}/>
                        <InputError className="mt-2" message={errors.anaf_certificate_password}/>
                    </div>
                </>
            )}

            {data.country_code === 'HU' && (
                <>
                    <div className="mb-4">
                        <InputLabel htmlFor="nav_user_id" value="NAV User ID"/>
                        <TextInput id="nav_user_id" className="mt-1 block w-full" value={data.nav_user_id}
                                   onChange={(e) => setData('nav_user_id', e.target.value)}/>
                        <InputError className="mt-2" message={errors.nav_user_id}/>
                    </div>
                    <div className="mb-4">
                        <InputLabel htmlFor="nav_exchange_key" value="NAV Exchange Key"/>
                        <TextInput id="nav_exchange_key" className="mt-1 block w-full" value={data.nav_exchange_key}
                                   onChange={(e) => setData('nav_exchange_key', e.target.value)}/>
                        <InputError className="mt-2" message={errors.nav_exchange_key}/>
                    </div>
                </>
            )}

              <div className="mb-4">
                  <InputLabel htmlFor="phone" value="Phone"/>
                  <TextInput id="phone" name="phone" value={data.phone} className="mt-1 block w-full"
                             autoComplete="tel" onChange={(e) => setData('phone', e.target.value)} required/>
                  <InputError className="mt-2" message={errors.phone}/>
              </div>

              <PrimaryButton type="submit" disabled={processing}>
                  Submit Vendor Application
              </PrimaryButton>
          </form>
      )}
    </section>
  );
}
