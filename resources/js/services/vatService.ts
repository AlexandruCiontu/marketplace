const vatRates: Record<string, number> = {
  RO: 0.19,
  default: 0.2,
};

const vatService = {
  calculate(
    price: number,
    rateType: string = 'standard',
    countryCode: string = 'RO'
  ) {
    let vatRate = vatRates[countryCode] ?? vatRates.default;
    if (rateType === 'zero') {
      vatRate = 0;
    }
    const vat = price * vatRate;
    return {
      gross: price + vat,
      vat,
    };
  },
};

if (typeof window !== 'undefined') {
  // @ts-ignore - extend global object
  window.vatService = vatService;
}

export default vatService;
