const vatRates: Record<string, number> = {
  RO: 19,
  default: 20,
};

const vatService = {
  calculate(
    price: number,
    rateType: string = 'standard_rate',
    countryCode: string = 'RO'
  ) {
    let vatRate = vatRates[countryCode] ?? vatRates.default;
    if (rateType === 'super_reduced_rate') {
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
