import { useEffect, useState } from 'react';
import vatService from '@/services/vatService';

export function useVatRate(
  countryCode: string,
  rateType: string = 'standard_rate'
) {
  const [rate, setRate] = useState<number>(0);

  useEffect(() => {
    let cancelled = false;
    vatService.getRate(countryCode, rateType).then((r) => {
      if (!cancelled) {
        setRate(r);
      }
    });
    return () => {
      cancelled = true;
    };
  }, [countryCode, rateType]);

  return rate;
}
