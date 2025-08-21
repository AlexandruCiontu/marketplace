const cache: Record<string, number> = {};

async function getRate(
  countryCode: string,
  rateType: string = 'standard_rate'
): Promise<number> {
  const key = `${countryCode}_${rateType}`;
  if (cache[key] !== undefined) {
    return cache[key];
  }
  const response = await fetch(
    `/api/vat-rate?country_code=${countryCode}&rate_type=${rateType}`
  );
  if (!response.ok) {
    cache[key] = 0;
    return 0;
  }
  const data = await response.json();
  const rate = data.rate ?? 0;
  cache[key] = rate;
  return rate;
}

function calculateVatIncludedPrice(net: number, rate: number): number {
  return +(net * (1 + rate / 100)).toFixed(2);
}

function calculateVatAmount(net: number, rate: number): number {
  return +(net * (rate / 100)).toFixed(2);
}

export default { getRate, calculateVatIncludedPrice, calculateVatAmount };
