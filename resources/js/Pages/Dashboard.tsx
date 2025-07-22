import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {Head, usePage} from '@inertiajs/react';
import {PageProps} from '@/types';

export default function Dashboard() {
  const { countryCode } = usePage<PageProps>().props

  return (
    <AuthenticatedLayout
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Dashboard
        </h2>
      }
    >
      <Head title="Dashboard"/>

      <div className="py-12">
        <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
            <div className="p-6 text-gray-900 dark:text-gray-100 space-y-2">
              <p>You're logged in!</p>
              <p>Country code: {countryCode}</p>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
