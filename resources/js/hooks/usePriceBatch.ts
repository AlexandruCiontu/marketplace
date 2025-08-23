import { useEffect, useMemo, useRef, useState } from "react";

export type PriceBreakdown = { net: number; vat: number; gross: number; rate: number };

export function stableKeyFromHit(hit: any): string {
  return String(hit?.id ?? hit?.slug ?? "");
}
// mic cache in-memory pentru dedupe între rerenderi
const pageCache = new Map<string, PriceBreakdown>();

export default function usePriceBatch(hits: any[]) {
  const [prices, setPrices] = useState<Record<string, PriceBreakdown>>({});
  const abortRef = useRef<AbortController | null>(null);

  const keys = useMemo(() => {
    const ks = hits
      .filter((h) => h?.price_gross == null)
      .map(h => stableKeyFromHit(h))
      .filter(Boolean) as string[];
    return Array.from(new Set(ks));
  }, [hits]);

  useEffect(() => {
    if (!keys.length) { setPrices({}); return; }

    const miss = keys.filter(k => !pageCache.has(k));
    if (!miss.length) {
      const obj: Record<string, PriceBreakdown> = {};
      keys.forEach(k => { const v = pageCache.get(k); if (v) obj[k] = v; });
      setPrices(obj);
      return;
    }

    const ctrl = new AbortController();
    abortRef.current?.abort();
    abortRef.current = ctrl;

    const params = new URLSearchParams();
    miss.forEach(k => params.append("ids[]", k));

    fetch(`/api/vat/price-batch?${params.toString()}`, {
      headers: { Accept: "application/json" },
      signal: ctrl.signal,
    })
      .then(r => r.ok ? r.json() : Promise.reject(r))
      .then((json: Record<string, PriceBreakdown>) => {
        for (const [k, v] of Object.entries(json || {})) pageCache.set(k, v);
        setPrices(p => ({ ...p, ...(json || {}) }));
      })
      .catch(err => { if (err?.name !== "AbortError") console.error("price-batch failed", err); });

    return () => { ctrl.abort(); };
  }, [keys.join("|")]);

  return { prices, keyFor: stableKeyFromHit };
}
