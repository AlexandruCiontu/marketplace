import { usePage } from '@inertiajs/react';

export default function CountryBadge() {
  const { props } = usePage<{ countryCode: string; vatLockedToAddress: boolean }>();
  return (
    <div className="text-sm opacity-70">
      VAT Country: {props.countryCode}{' '}
      {props.vatLockedToAddress && '(from default shipping address)'}
    </div>
  );
}
