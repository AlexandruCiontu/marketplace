import React, { useEffect, useMemo, useRef, useState } from "react";

type Props = {
  title: string;
  items: React.ReactNode[];
  className?: string;
};

export default function Carousel({ title, items, className }: Props) {
  const listRef = useRef<HTMLDivElement | null>(null);
  const [page, setPage] = useState(0);

  const pages = useMemo(() => {
    if (!listRef.current) return 0;
    const el = listRef.current;
    return Math.max(1, Math.round(el.scrollWidth / el.clientWidth));
  }, [items.length]);

  useEffect(() => {
    const el = listRef.current;
    if (!el) return;
    const onScroll = () => {
      setPage(Math.round(el.scrollLeft / el.clientWidth));
    };
    el.addEventListener("scroll", onScroll, { passive: true });
    return () => el.removeEventListener("scroll", onScroll);
  }, []);

  const scrollByPage = (dir: 1 | -1) => {
    const el = listRef.current;
    if (!el) return;
    el.scrollTo({ left: (page + dir) * el.clientWidth, behavior: "smooth" });
  };

  if (!items?.length) return null;

  return (
    <section className={`my-8 ${className || ""}`}>
      <div className="mb-3 flex items-center justify-between">
        <h3 className="text-lg font-semibold">{title}</h3>
        <div className="flex gap-2">
          <button
            onClick={() => scrollByPage(-1)}
            className="rounded-full border px-3 py-1 disabled:opacity-40"
            disabled={page <= 0}
            aria-label="Previous"
          >
            ‹
          </button>
          <button
            onClick={() => scrollByPage(1)}
            className="rounded-full border px-3 py-1 disabled:opacity-40"
            disabled={page >= pages - 1}
            aria-label="Next"
          >
            ›
          </button>
        </div>
      </div>

      <div
        ref={listRef}
        className="flex overflow-x-auto scroll-smooth snap-x snap-mandatory gap-4 pb-2"
      >
        <div className="shrink-0 snap-start w-full grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
          {items.slice(0, 10)}
        </div>
        {items.length > 10 && (
          <div className="shrink-0 snap-start w-full grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            {items.slice(10, 20)}
          </div>
        )}
        {items.length > 20 && (
          <div className="shrink-0 snap-start w-full grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            {items.slice(20, 30)}
          </div>
        )}
      </div>

      <div className="mt-2 flex items-center justify-center gap-2">
        {Array.from({ length: pages || 1 }).map((_, i) => (
          <button
            key={i}
            onClick={() => {
              const el = listRef.current;
              if (!el) return;
              el.scrollTo({ left: i * el.clientWidth, behavior: "smooth" });
            }}
            className={`h-2 w-2 rounded-full ${
              i === page ? "bg-slate-900" : "bg-slate-300"
            }`}
            aria-label={`Go to slide ${i + 1}`}
          />
        ))}
      </div>
    </section>
  );
}

