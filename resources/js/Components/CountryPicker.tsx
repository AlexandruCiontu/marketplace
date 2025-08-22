import { router, usePage } from '@inertiajs/react';

type Props = {
  className?: string;
};

export default function CountryPicker({ className }: Props) {
  const { props } = usePage<{ countryCode: string }>();

  const change = async (e: React.ChangeEvent<HTMLSelectElement>) => {
    const code = e.target.value;
    await fetch('/api/country/select', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content,
      },
      body: JSON.stringify({ country_code: code }),
      credentials: 'same-origin',
    });
    router.reload({ only: ['countryCode', 'cart', 'miniCartItems'] });
  };

  return (
    <select value={props.countryCode} onChange={change} className={className}>
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

