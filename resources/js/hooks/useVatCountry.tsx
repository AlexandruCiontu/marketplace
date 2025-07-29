import { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import axios from 'axios';

interface VatCountryContextValue {
  countryCode: string;
  updateCountry: (code: string) => void;
}

const VatCountryContext = createContext<VatCountryContextValue>({
  countryCode: 'RO',
  updateCountry: () => {},
});

export function VatCountryProvider({ children }: { children: ReactNode }) {
  const [countryCode, setCountryCode] = useState<string>('RO');

  useEffect(() => {
    axios.get('/api/vat-country').then(res => {
      setCountryCode(res.data.country_code);
    });
  }, []);

  const updateCountry = (code: string) => {
    axios.post('/api/vat-country', { country_code: code }).then(res => {
      setCountryCode(res.data.country_code);
    });
  };

  return (
    <VatCountryContext.Provider value={{ countryCode, updateCountry }}>
      {children}
    </VatCountryContext.Provider>
  );
}

export function useVatCountry() {
  return useContext(VatCountryContext);
}
