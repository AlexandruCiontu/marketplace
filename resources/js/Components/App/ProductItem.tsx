import React, { useEffect, useState } from "react";
import { ProductListItem } from "@/types";
import { Link, useForm } from "@inertiajs/react";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import type { PriceBreakdown } from "@/hooks/usePriceBatch";
import { stableKeyFromHit } from "@/hooks/usePriceBatch";

type Props = {
  product: ProductListItem;
  price?: PriceBreakdown;
};

const fmt = (v?: number) => (typeof v === "number" ? v : undefined);

export default function ProductItem({ product, price }: Props) {
  const form = useForm<{ option_ids: Record<string, number>; quantity: number }>(
    {
      option_ids: {},
      quantity: 1,
    }
  );

  const addToCart = () => {
    form.post(route("cart.store", product.id), {
      preserveScroll: true,
      preserveState: true,
      onError: (err) => {
        console.error(err);
      },
    });
  };

  const key: string = stableKeyFromHit(product);
  const [localPrice, setLocalPrice] = useState<PriceBreakdown | undefined>();

  useEffect(() => {
    if (price || !key) return;
    const params = new URLSearchParams();
    params.append("ids[]", key);
    fetch(`/api/vat/price-batch?${params.toString()}`, {
      headers: { Accept: "application/json" },
    })
      .then((r) => (r.ok ? r.json() : Promise.reject(r)))
      .then((json) => setLocalPrice(json?.[key]))
      .catch((e) => console.error("single price fetch failed", e));
  }, [key, !!price]);

  const net = Number((product as any).price ?? 0);
  const serverGross =
    product?.price_gross != null ? Number(product.price_gross) : undefined;
  const serverRate =
    product?.vat_rate != null ? Number(product.vat_rate) : undefined;

  const shownGross = price?.gross ?? localPrice?.gross ?? serverGross;
  const shownRate = price?.rate ?? localPrice?.rate ?? serverRate;
  const shownVat =
    price?.vat ?? localPrice?.vat ?? (shownGross != null ? +(shownGross - net).toFixed(2) : undefined);

  const amount = fmt(shownGross ?? net);

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
            {typeof amount === "number" ? (
              <CurrencyFormatter amount={amount} />
            ) : (
              "—"
            )}
            {shownGross != null && (
              <span className="block text-xs text-muted-foreground">
                Includes VAT {shownVat?.toFixed(2)}{shownRate != null ? ` (${shownRate}%)` : ""}
              </span>
            )}
          </span>
        </div>
      </div>
    </div>
  );
}
