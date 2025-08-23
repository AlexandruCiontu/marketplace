import { usePage } from '@inertiajs/react';

export function useVatCountry(): string {
  const page = usePage<{ vatCountry?: string; countryCode?: string }>();
  const code = page.props.vatCountry ?? page.props.countryCode;
  return (code ?? '').toUpperCase();
}
