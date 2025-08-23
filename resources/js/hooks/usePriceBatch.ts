import { useEffect, useMemo, useRef, useState } from 'react';

export type PriceBreakdown = {
  net: number;
  vat: number;
  gross: number;
  rate: number;
  currency?: string;
};

type Hit = {
  id: string;
  slug?: string;
};

type State = Record<string, PriceBreakdown>;

const isNumericId = (v: string) => /^\d+$/.test(v);

export function usePriceBatch(hits: Hit[], deps: unknown[] = []) {
  const [prices, setPrices] = useState<State>({});
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const { queryString, backMap } = useMemo(() => {
    const params = new URLSearchParams();
    const back: Record<string, string> = {};
    for (const h of hits) {
      const key = isNumericId(h.id) ? h.id : h.slug ?? h.id;
      params.append('ids[]', key);
      back[String(key)] = h.id;
    }
    return { queryString: params.toString(), backMap: back };
  }, [JSON.stringify(hits.map(h => ({ id: h.id, slug: h.slug })))]);

  const abortRef = useRef<AbortController | null>(null);

  const fetchNow = async () => {
    if (!hits.length) {
      setPrices({});
      return;
    }
    setLoading(true);
    setError(null);

    abortRef.current?.abort();
    abortRef.current = new AbortController();

    try {
      const res = await fetch(`/api/vat/price-batch?${queryString}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        signal: abortRef.current.signal,
      });
      if (!res.ok) throw new Error(`price-batch ${res.status}`);
      const data: Record<string, PriceBreakdown> = await res.json();
      const mapped: State = {};
      for (const [k, v] of Object.entries(data)) {
        const original = backMap[k] ?? k;
        mapped[original] = v;
      }
      setPrices(mapped);
    } catch (e: any) {
      if (e?.name !== 'AbortError') {
        setError(e?.message ?? 'price-batch failed');
      }
    } finally {
      setLoading(false);
    }
  };

  const depsKey = JSON.stringify(deps);

  useEffect(() => {
    fetchNow();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [queryString, depsKey]);

  return { prices, loading, error, refresh: fetchNow };
}

