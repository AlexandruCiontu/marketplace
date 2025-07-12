import React from 'react';
import {Department, PageProps, PaginationProps, Product, ProductListItem} from "@/types";
import {Head} from "@inertiajs/react";
import ProductListing from "@/Components/App/ProductListing";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

function Index(
  {
    appName,
    department,
    products
  }: PageProps<{
    department: Department,
    products: PaginationProps<ProductListItem>,
  }>) {
  return (
    <AuthenticatedLayout>
      <Head>
        <title>{department.name}</title>
        <meta name="title" content={department.meta_title}/>
        <meta name="description" content={department.meta_description}/>
        <link rel="canonical" href={route('product.byDepartment', department.slug)}/>

        <meta property="og:title" content={department.name}/>
        <meta property="og:description" content={department.meta_description}/>
        <meta property="og:url" content={route('product.byDepartment', department.slug)}/>
        <meta property="og:type" content="website"/>
        <meta property="og:site_name" content={appName}/>
      </Head>

      <div className="container mx-auto">
        <div className="hero bg-base-200 min-h-[120px]">
          <div className="hero-content text-center">
            <div className="max-w-lg">
              <h1 className="text-5xl font-bold">
                {department.name}
              </h1>
            </div>
          </div>
        </div>

        <ProductListing products={products}/>
      </div>
    </AuthenticatedLayout>
  );
}

export default Index;
