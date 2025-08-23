import React, { useEffect, useMemo, useRef, useState } from 'react';
import ErrorBoundary from '@/Components/ErrorBoundary';
import StarRating from '@/Components/StarRating';
import ReviewList from '@/Components/ReviewList';
import ReviewForm from '@/Components/ReviewForm';

type Review = {
  id: number;
  rating: number;
  body?: string;
  created_at: string;
  user: { id: number; name: string };
};

type Product = {
  id: number;
  name?: string;
  slug?: string;
  description?: string;
  image_url?: string | null;
  images?: Array<{ url: string }>;
  price_gross?: number | null;
  currency?: string | null;
  reviews?: Review[];
  reviews_count?: number | null;
  reviews_avg_rating?: number | null;
  rating_average?: number | null;
};

export default function Show(props: any) {
  const product: Product = (props?.product ?? {}) as Product;
  const allReviews: Review[] = Array.isArray(props?.all_reviews)
    ? props.all_reviews
    : Array.isArray(product?.reviews)
      ? product.reviews
      : [];

  const images = Array.isArray(product?.images) ? product.images : [];
  const reviewsCount = Number(
    product?.reviews_count ?? allReviews?.length ?? 0,
  ) || 0;
  const ratingAvg = Number(
    product?.rating_average ?? product?.reviews_avg_rating ?? 0,
  ) || 0;

  const reviewsRef = useRef<HTMLDivElement | null>(null);
  const [showAll, setShowAll] = useState(false);
  const openAll = () => {
    setShowAll(true);
    setTimeout(
      () => reviewsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' }),
      0,
    );
  };

  const priceCents = Number(product?.price_gross ?? 0);
  const currency = product?.currency || 'RON';
  const formattedPrice = useMemo(() => {
    try {
      return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency,
      }).format((priceCents || 0) / 100);
    } catch {
      return `${(priceCents || 0) / 100} ${currency}`;
    }
  }, [priceCents, currency]);

  useEffect(() => {
    if (typeof window !== 'undefined' && window.location.hash === '#reviews') {
      openAll();
    }
  }, []);

  return (
    <ErrorBoundary>
      <div className="container mx-auto p-4 space-y-8">
        <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
          <div>
            {product?.image_url ? (
              <img
                src={product.image_url}
                alt={product?.name ?? 'Product'}
                className="w-full rounded-xl border object-contain"
              />
            ) : (
              <div className="grid aspect-[4/3] w-full place-items-center rounded-xl border text-slate-500">
                No image
              </div>
            )}
            {images.length > 0 && (
              <div className="mt-3 flex gap-2 overflow-x-auto">
                {images.map((im: any, i: number) => (
                  <img
                    key={i}
                    src={im?.url}
                    className="h-16 w-16 rounded border object-cover"
                  />
                ))}
              </div>
            )}
          </div>

          <div>
            <h1 className="text-2xl font-semibold">{product?.name ?? 'Product'}</h1>

            <button
              type="button"
              onClick={openAll}
              className="mt-3 inline-flex items-center gap-3 rounded-xl border px-4 py-2"
            >
              <StarRating value={ratingAvg} />
              <div className="text-sm">
                {reviewsCount} {reviewsCount === 1 ? 'review' : 'reviews'} • average
                {` ${ratingAvg.toFixed(1)}/5`}
              </div>
            </button>

            <div className="mt-4 text-2xl font-bold">{formattedPrice}</div>
          </div>
        </div>

        <section>
          <h2 className="text-lg font-semibold">About the Item</h2>
          <p className="mt-2 text-slate-700">{product?.description ?? '—'}</p>
        </section>

        <section ref={reviewsRef} id="reviews" className="space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-lg font-semibold">Reviews</h2>
            <div className="text-sm opacity-70">
              {reviewsCount} reviews • average {ratingAvg.toFixed(1)}/5
            </div>
          </div>

          <ReviewList
            reviews={Array.isArray(allReviews) ? allReviews : []}
            limit={showAll ? undefined : 5}
          />

          {props?.can_review && !props?.already_reviewed && (
            <div className="mt-6">
              <h3 className="mb-2 font-medium">Leave a review</h3>
              <ReviewForm postUrl={`/products/${product?.id}/reviews`} />
            </div>
          )}
        </section>
      </div>
    </ErrorBoundary>
  );
}

