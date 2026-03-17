
import './page/stripe-plugin-settings'
import './components/stripe-settings-icon'

const { Module } = Shopware;

Module.register('stripe-settings', {
    name: 'stripe-settings',
    type: 'plugin',
    color: '#9AA8B5',
    title: "stripe-shopware-payment-config-module",
    iconComponent: 'stripe-settings-icon',

    routeMiddleware(next, currentRoute) {
        next(currentRoute);
    },

    routes: {
        view: {
            component: 'stripe-plugin-settings',
            path: 'view',
            meta: {
                parentPath: 'sw.settings.index',
            },
        }
    },

    settingsItem: {
        group: 'plugins',
        to: 'stripe.settings.view',
        backgroundEnabled: false,
        iconComponent: 'stripe-settings-icon',
        label: 'stripe-shopware-payment-config-module.stripe-shopware-payment',
    },

    snippets: {
        "de-DE": {
            "stripe-shopware-payment-config-module": {
                "stripe-shopware-payment": "Stripe"
            }
        },
        "en-GB": {
            "stripe-shopware-payment-config-module": {
                "stripe-shopware-payment": "Stripe"
            }
        }
    },
});