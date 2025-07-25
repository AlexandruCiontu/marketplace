import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import VendorDetails from '@/Pages/Profile/Partials/VendorDetails';

export default function Details() {
  return (
    <AuthenticatedLayout
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          Vendor Details
        </h2>
      }
    >
      <Head title="Vendor Details" />

      <div className="py-8">
        <div className="mx-auto max-w-2xl bg-white p-4 shadow sm:rounded-lg dark:bg-gray-800">
          <VendorDetails />
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
