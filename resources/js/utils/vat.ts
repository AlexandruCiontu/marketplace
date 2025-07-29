import ratesData from '@data/rates.json';

const rates = (ratesData as any).rates as Record<string, any>;

export function getVatRate(
  countryCode: string,
  type: 'standard' | 'reduced' | 'reduced2' = 'standard'
): number {
  const fallback = rates['RO'];
  const country = rates[countryCode] ?? fallback;
  if (!country) return 0.19; // default
  switch (type) {
    case 'reduced':
      return country.reduced_rate ?? country.standard_rate ?? fallback.standard_rate;
    case 'reduced2':
      return (
        country.reduced_rate_alt ?? country.reduced_rate ?? country.standard_rate ?? fallback.standard_rate
      );
    default:
      return country.standard_rate ?? fallback.standard_rate;
  }
}

export function calculateVatIncludedPrice(net: number, rate: number): number {
  return +(net * (1 + rate / 100)).toFixed(2);
}

export function calculateVatAmount(net: number, rate: number): number {
  return +(net * (rate / 100)).toFixed(2);
}
