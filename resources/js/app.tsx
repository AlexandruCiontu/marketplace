import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot, hydrateRoot } from 'react-dom/client';
import { VatCountryProvider } from './hooks/useVatCountry';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        if (import.meta.env.SSR) {
            hydrateRoot(el, (
                <VatCountryProvider>
                    <App {...props} />
                </VatCountryProvider>
            ));
            return;
        }

        createRoot(el).render(
            <VatCountryProvider>
                <App {...props} />
            </VatCountryProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
