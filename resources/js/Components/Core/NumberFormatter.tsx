import React from 'react';

function NumberFormatter({amount}: {
    amount: number,
  }) {
  return new Intl.NumberFormat('en-US').format(amount)
}

export default NumberFormatter;
