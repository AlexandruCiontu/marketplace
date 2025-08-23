import { useEffect, useMemo, useState } from "react";

export type PriceBreakdown = { net: number; vat: number; gross: number; rate: number };

export function stableKeyFromHit(hit: any): string {
  // Typesense: objectID (string) e "id"-ul documentului. Fallback pe id/slug.
  const raw = hit?.objectID ?? hit?.id ?? hit?.slug ?? "";
  return String(raw);
}

export default function usePriceBatch(hits: any[]) {
  const [prices, setPrices] = useState<Record<string, PriceBreakdown>>({});

  const keys = useMemo(
    () => Array.from(new Set(hits.map(stableKeyFromHit).filter(Boolean))),
    [hits]
  );

  useEffect(() => {
    if (!keys.length) { setPrices({}); return; }

    const params = new URLSearchParams();
    keys.forEach(k => params.append("ids[]", k));

    fetch(`/api/vat/price-batch?${params.toString()}`, { headers: { Accept: "application/json" } })
      .then(r => r.ok ? r.json() : Promise.reject(r))
      .then((json) => { setPrices(json || {}); })
      .catch(err => { console.error("price-batch failed", err); setPrices({}); });
  // stringify pentru a evita rerulări inutile
  }, [keys.join("|")]);

  return { prices, keyFor: stableKeyFromHit };
}
