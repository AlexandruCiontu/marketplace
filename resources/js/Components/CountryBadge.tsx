import { usePage } from '@inertiajs/react';

export default function CountryBadge() {
  const { props } = usePage<{ countryCode: string }>();
  return (
    <div className="text-sm opacity-70">
      VAT Country: {props.countryCode} (from default shipping address)
    </div>
  );
}
