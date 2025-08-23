import React from "react";
import StarRating from "@/Components/StarRating";

type CardProps = {
  id: number | string;
  name: string;
  slug: string;
  image_url?: string | null;
  price_gross: number;
  currency?: string;
  rating_average?: number;
  reviews_count?: number;
  badge?: string;
};

export default function ProductCardMini(p: CardProps) {
  return (
    <a
      href={`/product/${p.slug}`}
      className="group block rounded-2xl border p-3 hover:shadow-sm transition bg-white"
    >
      <div className="relative aspect-[4/3] overflow-hidden rounded-xl bg-slate-50">
        {p.badge && (
          <span className="absolute left-2 top-2 z-10 rounded-md bg-rose-600 px-2 py-0.5 text-xs text-white">
            {p.badge}
          </span>
        )}
        {p.image_url && (
          <img
            src={p.image_url}
            alt={p.name}
            className="h-full w-full object-contain transition-transform duration-300 group-hover:scale-[1.03]"
            loading="lazy"
          />
        )}
      </div>

      <div className="mt-3 line-clamp-2 min-h-[3.2rem]">{p.name}</div>

      <div className="mt-2 flex items-center gap-2">
        <StarRating value={p.rating_average ?? 0} />
        <span className="text-xs opacity-70">({p.reviews_count ?? 0})</span>
      </div>

      <div className="mt-2 text-lg font-semibold">
        {new Intl.NumberFormat(undefined, {
          style: "currency",
          currency: p.currency || "RON",
        }).format(p.price_gross / 100)}
      </div>
    </a>
  );
}

