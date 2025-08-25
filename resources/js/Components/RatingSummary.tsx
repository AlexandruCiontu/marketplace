import React from 'react';
import StarRating from '@/Components/StarRating';

type Props = {
  average: number;
  count: number;
  onClick?: () => void;
};

export default function RatingSummary({ average, count, onClick }: Props) {
  return (
    <button
      type="button"
      onClick={onClick}
      className="group flex items-center gap-3 rounded-xl border px-4 py-3 hover:shadow-sm transition"
      aria-label="Open all reviews"
    >
      <StarRating value={average || 0} size={22} />
      <div className="text-sm text-left">
        <div className="font-medium">
          {count} {count === 1 ? 'review' : 'reviews'} • average {Number(average || 0).toFixed(1)}/5
        </div>
        <div className="text-slate-500 group-hover:text-slate-600">
          Click to see all reviews & comments
        </div>
      </div>
    </button>
  );
}

