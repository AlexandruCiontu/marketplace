import { useForm } from '@inertiajs/react';

export default function ReviewForm({ postUrl }: { postUrl: string }) {
  const { data, setData, post, processing, errors, reset } = useForm({
    rating: 5,
    body: '',
  });

  return (
    <form
      onSubmit={e => {
        e.preventDefault();
        post(postUrl, { onSuccess: () => reset() });
      }}
      className="space-y-3"
    >
      <div>
        <label className="label">Rating</label>
        <select
          value={data.rating}
          onChange={e => setData('rating', Number(e.target.value))}
          className="select select-bordered"
        >
          {[5, 4, 3, 2, 1].map(v => (
            <option key={v} value={v}>
              {v}
            </option>
          ))}
        </select>
        {errors.rating && <div className="text-error text-sm mt-1">{errors.rating}</div>}
      </div>
      <div>
        <label className="label">Comment (optional)</label>
        <textarea
          value={data.body}
          onChange={e => setData('body', e.target.value)}
          className="textarea textarea-bordered w-full"
          rows={4}
          maxLength={2000}
        />
        {errors.body && <div className="text-error text-sm mt-1">{errors.body}</div>}
      </div>
      {errors.review && <div className="text-error text-sm">{errors.review}</div>}
      <button disabled={processing} className="btn btn-primary">
        Submit review
      </button>
    </form>
  );
}
