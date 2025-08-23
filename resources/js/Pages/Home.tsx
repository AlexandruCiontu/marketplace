import { PageProps, PaginationProps, Product } from '@/types';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import React from "react";
import {
  Configure,
  Pagination, SortBy,
  useHits
} from "react-instantsearch";
import ProductItem from "@/Components/App/ProductItem";
import FilterPanel from "@/Components/App/FilterPanel";
import NumberFormatter from "@/Components/Core/NumberFormatter";
import ProductListing from "@/Components/App/ProductListing";
import BannerSlider from "@/Components/App/BannerSlider";

function CustomHits() {
  const { hits, results } = useHits();
  type VatCalc = { price_net: number; vat_rate: number; vat_amount: number; price_gross: number };
  const [priceMap, setPriceMap] = React.useState<Record<string, VatCalc>>({});

  React.useEffect(() => {
    const ids = hits.map((h: any) => h.id);
    if (ids.length === 0) return;
    const params = new URLSearchParams();
    ids.forEach((id: any) => params.append('ids[]', String(id)));
    fetch(`/api/vat/price-batch?${params.toString()}`, { credentials: 'same-origin' })
      .then(res => res.json())
      .then((data: Record<string, VatCalc>) => {
        setPriceMap(data);
      });
  }, [hits]);

  if (!results || results.nbHits === 0) {
    return (
      <div className="w-full py-8 text-center">
        <div className="card bg-base-100 shadow-xl">
          <div className="card-body">
            <h2 className="text-xl font-semibold">No products found</h2>
            <p>Try adjusting your filters or search criteria</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <>
      <div className="mb-4 flex justify-between items-center">
        <p className="text-gray-500">
          Showing <span className="font-bold">
            <NumberFormatter amount={results.nbHits} />
          </span> products in <span className="font-bold">{results.processingTimeMS}ms</span>
        </p>

        <div className="flex items-center justify-end">
          Sort By:
          <SortBy
            classNames={{
              root: "flex ml-4 justify-end",
              select: "select select-bordered",
            }}
            items={[
              { label: "Relevance", value: "products_index" },
              { label: "Title Ascending", value: "products_index/sort/title:asc" },
              { label: "Title Descending", value: "products_index/sort/title:desc" },
              { label: "Price Ascending", value: "products_index/sort/price:asc" },
              { label: "Price Descending", value: "products_index/sort/price:desc" },
            ]}
          />
        </div>
      </div>
      <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3">
        {hits.map((hit: any) => {
          const calc = priceMap[String(hit.id)];
          return (
            <ProductItem
              product={calc ? { ...hit, ...calc } : hit}
              key={hit.id}
            />
          );
        })}
      </div>
    </>
  );
}

const sampleBanners = [
  {
    id: 1,
    title: "Welcome to CumparaTot",
    subtitle: "Discover amazing products at great prices",
    image: "https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80",
    link: "/products",
    buttonText: "Shop Now"
  },
  {
    id: 2,
    title: "New Arrivals",
    subtitle: "Check out our latest collection",
    image: "https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80",
    link: "/products?sort=newest",
    buttonText: "View New Arrivals"
  },
  {
    id: 3,
    title: "Special Offers",
    subtitle: "Up to 50% off on selected items",
    image: "https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80",
    link: "/products?sort=discount",
    buttonText: "View Deals"
  }
];

export default function Home({
                               products,
                             }: PageProps<{ products: PaginationProps<Product>; countryCode: string }>) {

  return (
    <AuthenticatedLayout>
      <Head title="Home" />
      <BannerSlider banners={sampleBanners} />

      <div className="container py-8 px-4 mx-auto">
        <div className="flex flex-col md:flex-row gap-8">
          <FilterPanel />

          <div className="flex-1">
            <Configure hitsPerPage={24} />
            <CustomHits />
            <Pagination
              classNames={{
                root: 'hidden justify-center md:flex',
                list: 'join mt-8',
                item: 'join-item btn',
                pageItem: '',
                link: '',
                selectedItem: 'btn-primary',
              }}
            />
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
