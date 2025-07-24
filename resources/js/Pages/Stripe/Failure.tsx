import {Head, Link} from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {XCircleIcon} from "@heroicons/react/24/outline";
import {PageProps} from "@/types";

export default function Failure({}: PageProps) {
  return (
    <AuthenticatedLayout>
      <Head title="Payment Failed" />
      <div className="w-[480px] mx-auto py-8 px-4">
        <div className="flex flex-col gap-2 items-center">
          <div className="text-6xl text-red-500">
            <XCircleIcon className="size-24" />
          </div>
          <div className="text-3xl">Payment Failed</div>
        </div>
        <div className="my-6 text-lg">
          Something went wrong with your payment. You can return to your cart or go back to your dashboard.
        </div>
        <div className="flex justify-between">
          <Link href={route('cart.index')} className="btn btn-primary">
            Back to Cart
          </Link>
          <Link href={route('dashboard')} className="btn">
            Dashboard
          </Link>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
