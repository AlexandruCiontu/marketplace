import { StarIcon as StarSolid } from '@heroicons/react/24/solid';
import { StarIcon as StarOutline } from '@heroicons/react/24/outline';

interface StarRatingProps {
  rating?: number | null;
}

export default function StarRating({ rating }: StarRatingProps) {
  const value = rating ?? 0;
  const fullStars = Math.floor(value);
  const hasHalfStar = value - fullStars >= 0.5;
  const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

  return (
    <div className="flex items-center gap-1">
      {/* Full stars */}
      {[...Array(fullStars)].map((_, i) => (
        <StarSolid key={`full-${i}`} className="w-5 h-5 text-yellow-400" />
      ))}

      {/* Half star */}
      {hasHalfStar && (
        <span className="relative w-5 h-5">
          <StarSolid
            className="w-5 h-5 text-yellow-400 absolute inset-0"
            style={{ clipPath: 'inset(0 50% 0 0)' }}
          />
          <StarOutline className="w-5 h-5 text-gray-300 absolute inset-0" />
        </span>
      )}

      {/* Empty stars */}
      {[...Array(emptyStars)].map((_, i) => (
        <StarOutline key={`empty-${i}`} className="w-5 h-5 text-gray-300" />
      ))}

      {/* Rating value */}
      <span className="ml-2 text-sm text-gray-600">{value.toFixed(1)} out of 5</span>
    </div>
  );
}
