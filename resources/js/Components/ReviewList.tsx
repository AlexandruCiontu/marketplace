import StarRating from '@/Components/Core/StarRating';

type Review = {
  id: number;
  rating: number;
  comment?: string | null;
  created_at: string;
  user: { id: number; name: string };
};

export default function ReviewList({ reviews }: { reviews: Review[] }) {
  if (!reviews?.length) return <p>Nu există recenzii încă.</p>;

  return (
    <div className="space-y-4">
      {reviews.map(r => (
        <div key={r.id} className="card card-bordered bg-base-100">
          <div className="card-body p-4">
            <div className="flex items-center justify-between">
              <StarRating rating={r.rating} />
              <span className="text-sm opacity-70">{new Date(r.created_at).toLocaleDateString()}</span>
            </div>
            {r.comment && <p className="mt-2">{r.comment}</p>}
            <div className="mt-2 text-sm opacity-70">— {r.user.name}</div>
          </div>
        </div>
      ))}
    </div>
  );
}
