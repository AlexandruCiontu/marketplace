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
  const [activeIndex, setActiveIndex] = useState(0);
  const images = Array.isArray(product?.images) ? product.images : [];

  type PricePayload = {
    net: number;
    vat_rate: number;
    vat_amount: number;
    gross: number;
    currency?: string;
    formatted_gross?: string;
  };

  const [price, setPrice] = useState<PricePayload | null>(null);
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
      fetchPrice().catch((e) => console.error(e));
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
      fetchPrice().catch((e) => console.error(e));
    },
    [product]
  );

  async function fetchPrice() {
    const idOrSlug = product?.slug ?? product?.id ?? initial?.slug ?? initial?.id;
    if (!idOrSlug || typeof window === "undefined") return;
    const qs = window.location.search;
    const res = await fetch(`/api/products/${idOrSlug}/price${qs}`, {
      headers: { Accept: "application/json" },
    });
    if (!res.ok) return;
    const data = await res.json();
    setPrice(data);
  }

  const MainImage: React.FC<{ src: string; alt: string }> = ({ src, alt }) => (
    <div className="relative w-full aspect-square rounded-2xl bg-base-200 overflow-hidden">
      <img
        src={src}
        alt={alt}
        className="absolute inset-0 w-full h-full object-contain"
        draggable={false}
      />
    </div>
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
          {/* === GALERIE FIX === */}
          <div className="grid grid-cols-12 gap-6">
            {/* Thumbnails rail (stânga) */}
            <div className="col-span-2">
              <div className="flex flex-col gap-3 max-h-[520px] overflow-auto pr-1">
                {images.map((url, i) => (
                  <button
                    key={i}
                    type="button"
                    onClick={() => setActiveIndex(i)}
                    className={`relative w-full aspect-square rounded-xl overflow-hidden border
            ${i === activeIndex ? 'border-primary ring-2 ring-primary/30' : 'border-base-300'}`}
                    aria-label={`thumbnail-${i}`}
                  >
                    <img src={url} alt={`thumb-${i}`} className="w-full h-full object-cover" />
                  </button>
                ))}
              </div>
            </div>

            {/* Imaginea mare (dreapta) */}
            <div className="col-span-10">
              {images[activeIndex] ? (
                <MainImage src={images[activeIndex]} alt={product.title} />
              ) : (
                <div className="w-full aspect-square rounded-2xl bg-base-200" />
              )}
              {/* thumbnails suplimentare sub imagine – opțional (ca în screenshot 3) */}
              <div className="mt-4 flex gap-3">
                {images.slice(0, 6).map((url, i) => (
                  <button
                    key={i}
                    type="button"
                    onClick={() => setActiveIndex(i)}
                    className={`relative w-24 h-24 rounded-xl overflow-hidden border
            ${i === activeIndex ? 'border-primary ring-2 ring-primary/30' : 'border-base-300'}`}
                  >
                    <img src={url} alt={`subthumb-${i}`} className="w-full h-full object-cover" />
                  </button>
                ))}
              </div>
            </div>
          </div>
          {/* === END GALERIE FIX === */}
        </div>

        <div>
          <h1 className="text-2xl font-semibold mb-2">{product.title}</h1>
          <div className="mb-6">
            {price ? (
              <>
                <div className="text-3xl font-bold">
                  {price.formatted_gross ?? `€${price.gross.toFixed(2)}`}
                </div>
                <div className="text-sm opacity-70">
                  Includes VAT: {`€${price.vat_amount.toFixed(2)}`} ({price.vat_rate}%)
                </div>
              </>
            ) : (
              <div className="text-3xl font-bold">
                €{Number(product.price ?? 0).toFixed(2)}
              </div>
            )}
          </div>

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

