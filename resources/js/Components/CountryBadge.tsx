import { usePage } from '@inertiajs/react';

export default function CountryBadge() {
  const { props } = usePage<{ countryCode: string; vatLockedToAddress: boolean }>();
  const iso2 = (props.countryCode || '').toUpperCase();

  return (
    <div className="text-sm opacity-70">
      VAT Country: {iso2} {props.vatLockedToAddress && '(from default shipping address)'}
    </div>
  );
}
