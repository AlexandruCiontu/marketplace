import React from 'react';

interface Review {
  id: number;
  rating: number;
  comment?: string | null;
  user: { id: number; name: string };
  created_at?: string;
}

export default function ReviewList({ reviews }: { reviews: Review[] }) {
  if (!reviews?.length) {
    return <p className="text-sm opacity-70">Încă nu există recenzii.</p>;
  }

  return (
    <ul className="space-y-4">
      {reviews.map((r) => (
        <li key={r.id} className="p-4 rounded-2xl border">
          <div className="flex items-center justify-between mb-2">
            <div className="font-medium">{r.user?.name ?? 'Utilizator'}</div>
            <div className="text-sm">⭐ {r.rating}/5</div>
          </div>
          {r.comment && <p className="text-sm">{r.comment}</p>}
        </li>
      ))}
    </ul>
  );
}
