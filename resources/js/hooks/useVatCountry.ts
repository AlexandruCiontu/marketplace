import { usePage } from '@inertiajs/react';

export function useVatCountry(): string {
  const page = usePage<{ countryCode: string; vatCountry?: string }>();
  return page.props.vatCountry ?? page.props.countryCode;
}
