import { usePage } from '@inertiajs/react';

export function useVatCountry() {
  const page = usePage<any>();
  return page.props?.vatCountry ?? page.props?.countryCode;
}
