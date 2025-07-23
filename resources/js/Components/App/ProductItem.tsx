import { ProductListItem } from "@/types";
import { Link, useForm } from "@inertiajs/react";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import { useEffect } from "react";

const getVatMultiplier = (country: string, vatType: string) => {
  const rates: Record<string, Record<string, number>> = {
    ro: { standard: 1.19 },
    hu: { standard: 1.27 },
    bg: { standard: 1.20 },
  };

  const defaultRate = 1.20;
  const countryRates = rates[country.toLowerCase()] ?? {};
  return countryRates[vatType] ?? defaultRate;
};

export default function ProductItem({ product }: { product: ProductListItem }) {

  const form = useForm<{
    option_ids: Record<string, number>;
    quantity: number;
  }>({
    option_ids: {},
    quantity: 1,
  });

  useEffect(() => {
    console.log("PRODUCT DEBUG:", product);
  }, [product]);

  const addToCart = () => {
    form.post(route("cart.store", product.id), {
      preserveScroll: true,
      preserveState: true,
      onError: (err) => {
        console.error(err);
      },
    });
  };

const country = localStorage.getItem('user_country') || 'ro';
const vatMultiplier = getVatMultiplier(country, product.vat_rate_type);
const displayPrice = Number(product.price ?? (product as any).net_raw ?? 0) * vatMultiplier;

  return (
    <div className="card bg-base-100 shadow">
      <Link href={route("product.show", product.slug)}>
        <figure>
          <img
            src={product.image}
            alt={product.title}
            className="w-full h-48 aspect-square object-contain"
          />
        </figure>
      </Link>
      <div className="card-body p-6">
        <Link href={route("product.show", product.slug)}>
          <h2 className="card-title text-sm">
            {product.title && product.title.length > 50
              ? product.title.substring(0, 90) + "..."
              : product.title}
          </h2>
        </Link>
        <p className="text-sm">
          by{" "}
          <Link
            href={route("vendor.profile", product.user_store_name)}
            className="hover:underline"
          >
            {product.user_name}
          </Link>{" "}
          in{" "}
          <Link
            href={route("product.byDepartment", product.department_slug)}
            className="hover:underline"
          >
            {product.department_name}
          </Link>
        </p>
        <div className="card-actions items-center justify-between mt-3">
          <button onClick={addToCart} className="btn btn-primary">
            Add to Cart
          </button>
          <span className="text-2xl">
            <CurrencyFormatter amount={displayPrice} />
          </span>
        </div>
      </div>
    </div>
  );
}
