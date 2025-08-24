import React, {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from "react";
import { Head } from "@inertiajs/react";

type Product = {
  id: number | string;
  title: string;
  slug: string;
  price: number;
  images?: string[];
};

type Props = {
  product?: Product;
};

async function fetchProduct(productIdOrSlug: string) {
  const qs = typeof window !== "undefined" ? window.location.search : "";
  const res = await fetch(`/api/products/${productIdOrSlug}${qs}`, {
    headers: { Accept: "application/json" },
  });
  if (!res.ok) {
    const text = await res.text();
    throw new Error(`Fetch product failed: ${res.status} ${text}`);
  }
  const json = await res.json();
  return json.data as Product;
}

const setQueryParam = (key: string, value: string | number) => {
  if (typeof window === "undefined") return;
  const url = new URL(window.location.href);
  url.searchParams.set(key, String(value));
  window.history.replaceState({}, "", url.toString());
};

export default function Show({ product: initial }: Props) {
  const [product, setProduct] = useState<Product | null>(initial ?? null);
  const pid = useMemo(
    () =>
      product?.slug
        ? product.slug
        : product?.id
        ? String(product.id)
        : "",
    [product]
  );

  useEffect(() => {
    const idOrSlug = initial?.slug ?? initial?.id ?? pid;
    if (idOrSlug) {
      fetchProduct(String(idOrSlug))
        .then(setProduct)
        .catch((e) => console.error(e));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onSelectOption = useCallback(
    (typeKey: string, optionValue: string | number) => {
      setQueryParam(typeKey, optionValue);
      const idOrSlug = product?.slug ?? product?.id;
      if (!idOrSlug) return;
      fetchProduct(String(idOrSlug))
        .then(setProduct)
        .catch((e) => console.error(e));
    },
    [product]
  );

  if (!product) {
    return (
      <div className="p-8">
        <Head title="Product" />
        <div className="animate-pulse">Loading product…</div>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-4">
      <Head title={product.title ?? "Product"} />
      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
          {Array.isArray(product.images) && product.images.length > 0 ? (
            <div className="space-y-3">
              <img
                src={product.images[0]}
                alt={product.title}
                className="rounded-2xl w-full"
              />
              <div className="grid grid-cols-4 gap-2">
                {product.images.slice(1).map((url, i) => (
                  <img
                    key={i}
                    src={url}
                    alt={`${product.title}-${i}`}
                    className="rounded-xl"
                  />
                ))}
              </div>
            </div>
          ) : (
            <div className="bg-base-200 rounded-2xl h-64 flex items-center justify-center">
              No images
            </div>
          )}
        </div>

        <div>
          <h1 className="text-2xl font-semibold mb-2">{product.title}</h1>
          <div className="text-xl mb-6">€{Number(product.price).toFixed(2)}</div>

          <div className="space-y-4">
            <div className="flex items-center gap-2">
              <span className="w-16">Color</span>
              <button
                className="btn btn-sm"
                onClick={() => onSelectOption("color", "red")}
              >
                red
              </button>
              <button
                className="btn btn-sm"
                onClick={() => onSelectOption("color", "yellow")}
              >
                yellow
              </button>
              <button
                className="btn btn-sm"
                onClick={() => onSelectOption("color", "green")}
              >
                green
              </button>
            </div>

            <div className="flex items-center gap-2">
              <span className="w-16">Size</span>
              <button
                className="btn btn-sm"
                onClick={() => onSelectOption("size", "small")}
              >
                S
              </button>
              <button
                className="btn btn-sm"
                onClick={() => onSelectOption("size", "medium")}
              >
                M
              </button>
              <button
                className="btn btn-sm"
                onClick={() => onSelectOption("size", "large")}
              >
                L
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

