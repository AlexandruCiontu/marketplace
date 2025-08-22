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
  // Ensure amount is a valid number
  const numericAmount = typeof amount === 'string' ? parseFloat(amount) : amount;

  // Debug log (remove after testing)
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
