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

export default { getRate };
