import InputError from '@/Components/Core/InputError';
import InputLabel from '@/Components/Core/InputLabel';
import PrimaryButton from '@/Components/Core/PrimaryButton';
import TextInput from '@/Components/Core/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import {Head, Link, useForm} from '@inertiajs/react';
import {countryCodes} from '@/data/countryCodes';
import {FormEventHandler} from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Register() {
  const {data, setData, post, processing, errors, reset} = useForm({
    name: '',
    email: '',
    country_code: '+40',
    phone: '',
    password: '',
    password_confirmation: '',
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    post(route('register'), {
      onFinish: () => reset('password', 'password_confirmation'),
    });
  };

  return (
    <AuthenticatedLayout>
      <Head title="Register"/>
      <div className={"p-8"}>
        <div className="card bg-white dark:bg-gray-800 shadow max-w-[420px] mx-auto">
          <div className="card-body">

            <h1 className={"text-2xl mb-6 text-center"}>Create new account</h1>

            <form onSubmit={submit}>
              <div>
                <InputLabel htmlFor="name" value="Name"/>

                <TextInput
                  id="name"
                  name="name"
                  value={data.name}
                  className="mt-1 block w-full"
                  autoComplete="name"
                  isFocused={true}
                  onChange={(e) => setData('name', e.target.value)}
                  required
                />

                <InputError message={errors.name} className="mt-2"/>
              </div>

              <div className="mt-4">
                <InputLabel htmlFor="email" value="Email"/>

                <TextInput
                  id="email"
                  type="email"
                  name="email"
                  value={data.email}
                  className="mt-1 block w-full"
                  autoComplete="username"
                  onChange={(e) => setData('email', e.target.value)}
                  required
                />

                <InputError message={errors.email} className="mt-2"/>
              </div>

              <div className="mt-4">
                <InputLabel htmlFor="country_code" value="Country Code"/>

                <select
                  id="country_code"
                  name="country_code"
                  value={data.country_code}
                  className="select select-bordered w-full mt-1"
                  onChange={(e) => setData('country_code', e.target.value)}
                  required>
                  {countryCodes.map((c) => (
                    <option key={c.code} value={c.code}>
                      {c.name} ({c.code})
                    </option>
                  ))}
                </select>

                <InputError message={errors.country_code} className="mt-2"/>
              </div>

              <div className="mt-4">
                <InputLabel htmlFor="phone" value="Phone"/>

                <TextInput
                  id="phone"
                  name="phone"
                  value={data.phone}
                  className="mt-1 block w-full"
                  autoComplete="tel"
                  onChange={(e) => setData('phone', e.target.value)}
                  required
                />

                <InputError message={errors.phone} className="mt-2"/>
              </div>

              <div className="mt-4">
                <InputLabel htmlFor="password" value="Password"/>

                <TextInput
                  id="password"
                  type="password"
                  name="password"
                  value={data.password}
                  className="mt-1 block w-full"
                  autoComplete="new-password"
                  onChange={(e) => setData('password', e.target.value)}
                  required
                />

                <InputError message={errors.password} className="mt-2"/>
              </div>

              <div className="mt-4">
                <InputLabel
                  htmlFor="password_confirmation"
                  value="Confirm Password"
                />

                <TextInput
                  id="password_confirmation"
                  type="password"
                  name="password_confirmation"
                  value={data.password_confirmation}
                  className="mt-1 block w-full"
                  autoComplete="new-password"
                  onChange={(e) =>
                    setData('password_confirmation', e.target.value)
                  }
                  required
                />

                <InputError
                  message={errors.password_confirmation}
                  className="mt-2"
                />
              </div>

              <div className="mt-4 flex items-center justify-end">
                <Link
                  href={route('login')}
                  className="link"
                >
                  Already registered?
                </Link>

                <PrimaryButton className="ms-4" disabled={processing}>
                  Register
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
