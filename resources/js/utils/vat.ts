import rates from '@/data/rates.json';

export function getVatRateForCountry(countryCode: string, rateType: string) {
  const country = (rates as any).rates?.[countryCode];
  if (!country) return 0;

  const direct = country[`${rateType}_rate`];
  if (direct !== undefined && direct !== false) {
    return direct;
  }
  if (rateType === 'reduced2') {
    const reduced = country.reduced_rate;
    if (reduced !== undefined && reduced !== false) {
      return reduced;
    }
  }
  return country.standard_rate ?? 0;
}

export function getVatRate(countryCode: string, rateType: string = 'standard'): number {
  if (rateType === 'zero') return 0;
  return getVatRateForCountry(countryCode, rateType);
}

export function calculateVatIncludedPrice(net: number, vatRate: number): number {
  return +(net + net * (vatRate / 100)).toFixed(2);
}

export function calculateVatAndGross(price: number, rateType: string = 'standard', countryCode: string = 'RO') {
  const rate = getVatRate(countryCode, rateType);
  const vat = +(price * rate / 100).toFixed(2);
  const gross = +(price + vat).toFixed(2);
  return { rate, vat, gross };
}
