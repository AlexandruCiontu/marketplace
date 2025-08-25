import React from 'react';

type Props = {
  value: number;
  size?: number;
  className?: string;
};

const Star = ({ size = 20 }: { size?: number }) => (
  <svg
    width={size}
    height={size}
    viewBox="0 0 20 20"
    fill="currentColor"
    aria-hidden="true"
  >
    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.285a1 1 0 00.95.69h3.454c.967 0 1.371 1.24.588 1.81l-2.796 2.03a1 1 0 00-.363 1.118l1.07 3.285c.3.921-.755 1.688-1.538 1.118l-2.796-2.03a1 1 0 00-1.175 0l-2.796 2.03c-.783.57-1.838-.197-1.538-1.118l1.07-3.285a1 1 0 00-.363-1.118L2.937 8.712c-.783-.57-.38-1.81.588-1.81h3.454a1 1 0 00.95-.69l1.12-3.285z" />
  </svg>
);

export default function StarRating({ value, size = 20, className }: Props) {
  const pct = Math.max(0, Math.min(100, (value / 5) * 100));

  return (
    <div className={`relative inline-block ${className || ''}`} style={{ lineHeight: 0 }}>
      <div className="flex text-slate-300">
        {Array.from({ length: 5 }).map((_, i) => <Star key={`g-${i}`} size={size} />)}
      </div>
      <div
        className="absolute top-0 left-0 overflow-hidden text-yellow-500"
        style={{ width: `${pct}%` }}
        aria-hidden="true"
      >
        <div className="flex">
          {Array.from({ length: 5 }).map((_, i) => <Star key={`y-${i}`} size={size} />)}
        </div>
      </div>
    </div>
  );
}

