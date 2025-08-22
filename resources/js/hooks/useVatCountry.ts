import { usePage } from '@inertiajs/react';

export function useVatCountry(): string {
  const page = usePage<{ countryCode: string }>();
  return page.props.countryCode;
}
