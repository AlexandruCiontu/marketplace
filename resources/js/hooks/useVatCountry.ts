import { usePage } from '@inertiajs/react';

export function useVatCountry(): string {
  const page = usePage<{ countryCode?: string }>();
  const code = page.props.countryCode ?? 'RO';
  return code.toUpperCase();
}
