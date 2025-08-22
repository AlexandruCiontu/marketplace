import { useState } from 'react';
import { router } from '@inertiajs/react';

type Props = {
  value: string;
  className?: string;
};

export default function CountryPicker({ value, className }: Props) {
  const [country, setCountry] = useState(value);

  const change = async (e: React.ChangeEvent<HTMLSelectElement>) => {
    const code = e.target.value;
    setCountry(code);
    await fetch('/api/country/select', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content,
      },
      body: JSON.stringify({ country_code: code }),
    });
    router.reload({ only: ['countryCode', 'vatCountry'] });
  };

  return (
    <select value={country} onChange={change} className={className}>
      {[
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE',
        'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT',
        'RO', 'SK', 'SI', 'ES', 'SE'
      ].map(code => (
        <option key={code} value={code}>{code}</option>
      ))}
    </select>
  );
}

