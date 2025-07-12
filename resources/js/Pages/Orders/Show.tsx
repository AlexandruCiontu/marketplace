import React from 'react';
import { Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { formatPrice } from '@/helpers';

interface VariationType {
  id: number;
  name: string;
}

interface VariationOption {
  id: number;
  name: string;
  variationType: VariationType;
}

interface OrderItem {
  id: number;
  product: {
    id: number;
    title: string;
    slug: string;
    featured_image_url?: string;
  };
  price: number;
  quantity: number;
  variationOptions: VariationOption[];
}

interface Country {
  id: number;
  name: string;
}

interface ShippingAddress {
  id: number;
  full_name: string;
  address1: string;
  address2?: string;
  city: string;
  state: string;
  zipcode: string;
  phone: string;
  country: Country;
}

interface Order {
  id: number;
  created_at: string;
  status: string;
  total_price: number;
  tracking_code?: string;
  tracking_code_added_at?: string;
  orderItems: OrderItem[];
  shippingAddress?: ShippingAddress;
}

// Helper function to get badge class based on order status
const getStatusBadgeClass = (status: string): string => {
  switch (status.toLowerCase()) {
    case 'completed':
      return 'badge-success';
    case 'processing':
      return 'badge-info';
    case 'pending':
      return 'badge-warning';
    case 'cancelled':
      return 'badge-error';
    case 'shipped':
      return 'badge-primary';
    case 'draft':
      return 'badge-ghost';
    default:
      return 'badge-neutral';
  }
};

export default function Show({ order }: { order: Order }) {
  // Calculate subtotal
  const subtotal = (order.orderItems || []).reduce(
    (acc, item) => acc + item.price * item.quantity,
    0
  );


  return (
    <AuthenticatedLayout>
      <Head title={`Order #${order.id}`} />

      <div className="container mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div className="mb-8 flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-semibold text-base-content">Order #{order.id}</h1>
            <p className="mt-2 text-sm text-base-content/70">
              Placed on {new Date(order.created_at).toLocaleDateString()}
            </p>
          </div>
          <div className="flex space-x-3">
            <Link
              href="/orders"
              className="btn btn-outline"
            >
              Back to Orders
            </Link>
            <Link
              href={`/orders/${order.id}/invoice`}
              target="_blank"
              className="btn btn-primary"
            >
              Print Invoice
            </Link>
          </div>
        </div>

        <div className="card bg-base-100 shadow mb-8">
          <div className="card-header px-4 py-5 bg-base-200">
            <h2 className="card-title text-base-content">Order Status</h2>
          </div>
          <div className="card-body border-t border-base-300 px-4 py-5">
            <div className="flex justify-between items-center">
              <div className="flex items-center space-x-4">
                <span className={`badge ${getStatusBadgeClass(order.status)} badge-lg`}>
                  {order.status}
                </span>
                {order.tracking_code && (
                  <div className="text-base-content/80">
                    <span className="font-medium">Tracking Code:</span> {order.tracking_code}
                  </div>
                )}
              </div>
              <div className="text-xl font-bold text-base-content">
                Total: {formatPrice(order.total_price)}
              </div>
            </div>
          </div>
        </div>

        {order.shippingAddress && (
          <div className="card bg-base-100 shadow mb-8">
            <div className="card-header px-4 py-5 bg-base-200">
              <h2 className="card-title text-base-content">Shipping Address</h2>
            </div>
            <div className="card-body border-t border-base-300 px-4 py-5">
              <div className="text-base-content/80">
                <p className="font-bold">{order.shippingAddress.full_name}</p>
                <p>{order.shippingAddress.address1}</p>
                {order.shippingAddress.address2 && <p>{order.shippingAddress.address2}</p>}
                <p>{order.shippingAddress.city}, {order.shippingAddress.state} {order.shippingAddress.zipcode}</p>
                <p>{order.shippingAddress.country.name}</p>
                <p>{order.shippingAddress.phone}</p>
              </div>
            </div>
          </div>
        )}

        <div className="card bg-base-100 shadow">
          <div className="card-header px-4 py-5 bg-base-200">
            <h2 className="card-title text-base-content">Order Items</h2>
          </div>
          <div className="card-body p-0 border-t border-base-300">
            <div className="overflow-x-auto">
              <table className="table table-zebra w-full">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Variations</th>
                    <th className="text-right">Price</th>
                    <th className="text-right">Quantity</th>
                    <th className="text-right">Total</th>
                  </tr>
                </thead>
                <tbody>

                  {(order.orderItems || []).map((item) => (
                    <tr key={item.id} className="hover">
                      <td className="py-4 pl-4 pr-3 text-sm">
                        <div className="flex items-center">
                          {item.product.featured_image_url && (
                            <div className="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 mr-4">
                              <img
                                src={item.product.featured_image_url}
                                alt={item.product.title}
                                className="h-full w-full object-cover object-center"
                              />
                            </div>
                          )}
                          <Link
                            href={`/product/${item.product.slug}`}
                            className="font-medium text-primary hover:underline"
                          >
                            {item.product.title}
                          </Link>
                        </div>
                      </td>
                      <td>
                        {item.variationOptions && item.variationOptions.length > 0 ? (
                          <div className="space-y-1">
                            {item.variationOptions.map((option) => (
                              <div key={option.id}>
                                <span className="font-medium">{option.variationType.name}:</span> {option.name}
                              </div>
                            ))}
                          </div>
                        ) : (
                          'N/A'
                        )}
                      </td>
                      <td className="text-right">
                        {formatPrice(item.price)}
                      </td>
                      <td className="text-right">
                        {item.quantity}
                      </td>
                      <td className="text-right font-medium">
                        {formatPrice(item.price * item.quantity)}
                      </td>
                    </tr>
                  ))}
                </tbody>
                <tfoot>
                  <tr>
                    <th colSpan={4} className="text-right">
                      Subtotal:
                    </th>
                    <td className="text-right">
                      {formatPrice(subtotal)}
                    </td>
                  </tr>
                  {/* We could add shipping, tax, discount here if available in the order object */}
                  <tr className="border-t-2 border-base-300 font-bold">
                    <th colSpan={4} className="text-right">
                      Total:
                    </th>
                    <td className="text-right">
                      {formatPrice(order.total_price)}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
