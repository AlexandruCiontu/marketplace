import { usePage } from '@inertiajs/react';

export function useVatCountry(): string {
  const page = usePage<{ countryCode?: string; vatCountry?: string }>();
  const code = page.props.vatCountry ?? page.props.countryCode ?? 'RO';
  return code.toUpperCase();
}
