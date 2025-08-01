import Checkbox from '@/Components/Core/Checkbox';
import InputError from '@/Components/Core/InputError';
import InputLabel from '@/Components/Core/InputLabel';
import PrimaryButton from '@/Components/Core/PrimaryButton';
import TextInput from '@/Components/Core/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import {Head, Link, useForm} from '@inertiajs/react';
import {FormEventHandler} from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Login({
                                status,
                                canResetPassword,
                              }: {
  status?: string;
  canResetPassword: boolean;
}) {
  const {data, setData, post, processing, errors, reset} = useForm({
    email: '',
    password: '',
    remember: false,
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    post(route('login'), {
      onFinish: () => reset('password'),
    });
  };

  return (
    <AuthenticatedLayout>
      <Head title="Log in"/>

      <div className={"p-8"}>
        <div className="card bg-white dark:bg-gray-800 shadow max-w-[420px] mx-auto">
          <div className="card-body">

            <h1 className={"text-2xl mb-6 text-center"}>Login to your account</h1>

            {status && (
              <div className="mb-4 text-sm font-medium text-green-600">
                {status}
              </div>
            )}

            <form onSubmit={submit}>
              <div>
                <InputLabel htmlFor="email" value="Email"/>

                <TextInput
                  id="email"
                  type="email"
                  name="email"
                  value={data.email}
                  className="mt-1 block w-full"
                  autoComplete="username"
                  isFocused={true}
                  onChange={(e) => setData('email', e.target.value)}
                />

                <InputError message={errors.email} className="mt-2"/>
              </div>

              <div className="mt-4">
                <InputLabel htmlFor="password" value="Password"/>

                <TextInput
                  id="password"
                  type="password"
                  name="password"
                  value={data.password}
                  className="mt-1 block w-full"
                  autoComplete="current-password"
                  onChange={(e) => setData('password', e.target.value)}
                />

                <InputError message={errors.password} className="mt-2"/>
              </div>

              <div className="mt-4 block">
                <label className="flex items-center">
                  <Checkbox
                    name="remember"
                    checked={data.remember}
                    onChange={(e) =>
                      setData('remember', e.target.checked)
                    }
                  />
                  <span className="ms-2 text-sm text-gray-600 dark:text-gray-400">
                      Remember me
                  </span>
                </label>
              </div>

              <div className="mt-4 flex items-center justify-end">
                {canResetPassword && (
                  <Link
                    href={route('password.request')}
                    className="link"
                  >
                    Forgot your password?
                  </Link>
                )}

                <PrimaryButton className="ms-4" disabled={processing}>
                  Log in
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
