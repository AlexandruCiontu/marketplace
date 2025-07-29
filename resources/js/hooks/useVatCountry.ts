import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export function useVatCountry() {
  const { countryCode } = usePage<PageProps>().props;
  return { countryCode: countryCode || 'RO' };
}
