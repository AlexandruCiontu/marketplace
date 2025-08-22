import { useForm } from '@inertiajs/react';
import React from 'react';
import { t } from '@/i18n/t';

interface Props {
  productId: number;
}

export default function ReviewForm({ productId }: Props) {
  const { data, setData, post, processing, errors, reset } = useForm({
    rating: 5 as number,
    comment: '' as string,
  });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    post(route('reviews.store', productId), {
      onSuccess: () => reset('comment'),
    });
  }

  return (
    <form onSubmit={submit} className="space-y-3">
      <div>
        <label className="block text-sm mb-1">{t('rating')}</label>
        <select
          className="select select-bordered w-full"
          value={data.rating}
          onChange={(e) => setData('rating', Number(e.target.value))}
        >
          {[5, 4, 3, 2, 1].map((r) => (
            <option key={r} value={r}>
              {r}
            </option>
          ))}
        </select>
        {errors.rating && (
          <p className="text-error text-sm mt-1">{errors.rating}</p>
        )}
      </div>

      <div>
        <label className="block text-sm mb-1">{t('commentOptional')}</label>
        <textarea
          className="textarea textarea-bordered w-full"
          rows={4}
          value={data.comment}
          onChange={(e) => setData('comment', e.target.value)}
          placeholder="Tell us what you thought about the product..."
        />
        {errors.comment && (
          <p className="text-error text-sm mt-1">{errors.comment}</p>
        )}
      </div>

      {(errors as any).review && (
        <p className="text-error text-sm">{t('buyersOnly')}</p>
      )}

      <button className="btn btn-primary" disabled={processing}>
        {t('submitReview')}
      </button>
    </form>
  );
}
