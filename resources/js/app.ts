import { createInertiaApp, router } from '@inertiajs/vue3';

const appName = 'Knowledge Vault';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    withApp: (app) => {
        if (typeof document !== 'undefined') {
            router.on('finish', (event) => {
                if (event.detail.visit.method !== 'get') {
                    router.flushAll();
                }
            });
        }
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#4B5563',
    },
});
