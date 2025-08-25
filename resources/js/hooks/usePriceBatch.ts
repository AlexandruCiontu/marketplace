import { useEffect, useMemo, useRef, useState } from "react";
import { useVatCountry } from "./useVatCountry";

export type PriceBreakdown = { net: number; vat: number; gross: number; rate: number };

// mic cache in-memory pentru dedupe între rerenderi
const pageCache = new Map<string, PriceBreakdown>();

export default function usePriceBatch(hits: any[]) {
  const [prices, setPrices] = useState<Record<string, PriceBreakdown>>({});
  const abortRef = useRef<AbortController | null>(null);
  const country = useVatCountry();

  const keys = useMemo(() => {
    const ks = hits
      .filter((h) => h?.price_gross == null)
      .map(h => String(h.id ?? ""))
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

    fetch(`/api/vat/price-batch`, {
      method: "POST",
      headers: { Accept: "application/json", "Content-Type": "application/json" },
      body: JSON.stringify({ ids: miss, country_code: country }),
      signal: ctrl.signal,
    })
      .then(r => r.ok ? r.json() : Promise.reject(r))
      .then((json: any) => {
        const map = json?.items || {};
        const out: Record<string, PriceBreakdown> = {};
        Object.entries(map).forEach(([k, v]: any) => {
          if (v && v.found) {
            out[k] = { net: v.net, vat: v.vat, gross: v.gross, rate: v.rate };
            pageCache.set(k, out[k]);
          }
        });
        setPrices(p => ({ ...p, ...out }));
      })
      .catch(err => { if (err?.name !== "AbortError") console.error("price-batch failed", err); });

    return () => { ctrl.abort(); };
  }, [keys.join("|")]);

  return { prices, keyFor: (hit: any) => String(hit.id ?? "") };
}
