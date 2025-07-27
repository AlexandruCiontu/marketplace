import React from 'react';

function CurrencyFormatter({
                             amount,
                             currency = 'EUR',
                             locale = 'en-EN' // default locale
                           }: {
  amount: number,
  currency?: string,
  locale?: string
}) {
  // Verificare că amount este un număr valid
  const numericAmount = typeof amount === 'string' ? parseFloat(amount) : amount;

  // Debug log (poți să-l scoți după testare)
  console.log('Amount formatted:', numericAmount);

  return (
    <>
      {new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(numericAmount)}
    </>
  );
}

export default CurrencyFormatter;
