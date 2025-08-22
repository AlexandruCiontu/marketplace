import ratesData from '@data/rates.json';

const rates = (ratesData as any).rates as Record<string, any>;

export function getVatRate(
  countryCode: string,
  type: 'standard_rate' | 'reduced_rate' | 'reduced_rate_alt' | 'super_reduced_rate' = 'standard_rate'
): number {
  const country = rates[countryCode] || {};
  const rate = country?.[type];
  if (typeof rate === 'number') {
    return rate;
  }
  return country?.standard_rate ?? 0;
}

export function calculateVatIncludedPrice(net: number, rate: number): number {
  return +(net * (1 + rate / 100)).toFixed(2);
}

export function calculateVatAmount(net: number, rate: number): number {
  return +(net * (rate / 100)).toFixed(2);
}
