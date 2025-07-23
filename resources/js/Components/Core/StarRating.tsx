import {StarIcon as StarSolid} from '@heroicons/react/24/solid';
import {StarIcon as StarOutline} from '@heroicons/react/24/outline';

export default function StarRating({ rating }: { rating: number }) {
  const full = Math.floor(rating);
  const empty = 5 - full;
  return (
    <div className="flex">
      {Array.from({ length: full }).map((_, i) => (
        <StarSolid key={i} className="w-5 h-5 text-yellow-400" />
      ))}
      {Array.from({ length: empty }).map((_, i) => (
        <StarOutline key={'e'+i} className="w-5 h-5 text-gray-300" />
      ))}
    </div>
  );
}
