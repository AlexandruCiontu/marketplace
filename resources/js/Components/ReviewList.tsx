import React from 'react';
import StarRating from '@/Components/StarRating';

type Review = {
  id: number;
  rating: number;
  body?: string;
  created_at: string;
  user: { id: number; name: string };
};

export default function ReviewList({ reviews, limit }: { reviews: Review[]; limit?: number }) {
  const items = typeof limit === 'number' ? reviews.slice(0, limit) : reviews;

  if (!reviews?.length) return <p>No reviews yet.</p>;

  return (
    <div className="space-y-4">
      {items.map(r => (
        <div key={r.id} className="rounded-xl p-4 border">
          <div className="flex items-center justify-between">
            <StarRating value={r.rating} />
            <span className="text-sm opacity-70">
              {new Date(r.created_at).toLocaleDateString()}
            </span>
          </div>
          {r.body && <p className="mt-2">{r.body}</p>}
          <div className="mt-2 text-sm opacity-70">— {r.user.name}</div>
        </div>
      ))}
    </div>
  );
}

