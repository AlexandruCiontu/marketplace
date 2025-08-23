import { useEffect, useMemo, useState } from "react";

export type BatchPrice = {
  price_net: number;
  vat_rate: number;
  vat_amount: number;
  price_gross: number;
};

/** Extrage o cheie stabilă dintr-un hit Typesense/Algolia/Eloquent. */
export function pickHitKey(hit: any): string {
  // încearcă, în ordinea asta: id, objectID (Algolia), document.id (Typesense),
  // slug (fallback), document.slug (alt fallback)
  const key =
    hit?.id ??
    hit?.objectID ??
    hit?.document?.id ??
    hit?.slug ??
    hit?.document?.slug ??
    "";
  return key ? String(key) : "";
}

export default function usePriceBatch(hits: any[]) {
  const keys = useMemo(() => {
    const k = Array.from(
      new Set(
        (hits || [])
          .map(pickHitKey)
          .map(String)
          .filter(Boolean)
      )
    );
    return k;
  }, [hits]);

  const [map, setMap] = useState<Record<string, BatchPrice>>({});

  useEffect(() => {
    if (!keys.length) {
      setMap({});
      return;
    }
    const qs = keys.map((k) => `ids[]=${encodeURIComponent(k)}`).join("&");
    fetch(`/api/vat/price-batch?${qs}`, { credentials: "same-origin" })
      .then((r) => (r.ok ? r.json() : Promise.reject(r)))
      .then((res) => setMap(res || {}))
      .catch(() => setMap({}));
    // stringify pt. a evita efecte false când se reface array-ul
  }, [JSON.stringify(keys)]);

  return map;
}
