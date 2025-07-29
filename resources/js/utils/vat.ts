import rates from '@/data/rates.json';

export function getVatRate(countryCode: string, rateType: string = 'standard'): number {
  const country = (rates as any).rates?.[countryCode] ?? (rates as any).rates?.RO;
  if (!country) return 0;

  switch (rateType) {
    case 'reduced2':
      return country.reduced_rate_alt ?? country.standard_rate ?? 0;
    case 'reduced':
      return country.reduced_rate ?? country.standard_rate ?? 0;
    case 'zero':
      return 0;
    default:
      return country.standard_rate ?? 0;
  }
}

export function calculateVatAndGross(price: number, rateType: string = 'standard', countryCode: string = 'RO') {
  const rate = getVatRate(countryCode, rateType);
  const vat = +(price * rate / 100).toFixed(2);
  const gross = +(price + vat).toFixed(2);
  return { rate, vat, gross };
}
