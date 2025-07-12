import React from 'react';
import {PageProps, PaginationProps, Product, ProductListItem, Vendor} from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {Head} from "@inertiajs/react";
import ProductListing from "@/Components/App/ProductListing";

function Profile(
  {
    vendor,
    products
  }: PageProps<{
    vendor: Vendor,
    products: PaginationProps<ProductListItem>
  }>) {
  return (
    <AuthenticatedLayout>
      <Head title={vendor.store_name + ' Profile Page'}/>

      <div
        className="hero min-h-[320px]"
        style={{
          backgroundImage: "url(https://img.daisyui.com/images/stock/photo-1507358522600-9f71e620c44e.webp)",
        }}>
        <div className="hero-overlay bg-opacity-60"></div>
        <div className="hero-content text-neutral-content text-center">
          <div className="max-w-md">
            <h1 className="mb-5 text-5xl font-bold">
              {vendor.store_name}
            </h1>
          </div>
        </div>
      </div>

      <ProductListing products={products}/>
    </AuthenticatedLayout>
  );
}

export default Profile;
