import './page/list';
import './page/settings';

Shopware.Module.register('price-drop-alert', {
    type: 'plugin',
    name: 'PriceDropAlert',
    title: 'price-drop-alert.general.mainMenuItemGeneral',
    description: 'Manage price drop subscriptions',
    color: '#3f8cff',
    icon: 'regular-bell',

    snippets: {
        'en-GB': {
            'price-drop-alert': {
                general: {
                    mainMenuItemGeneral: 'Price Drop Alerts',
                    descriptionTextModule: 'Customer subscriptions for product price changes',
                },
            },
        },
    },

    routes: {
        list: {
            component: 'price-drop-alert-list',
            path: 'list',
        },
        settings: {
            component: 'price-drop-alert-settings',
            path: 'settings',
        },
    },

    navigation: [{
        label: 'price-drop-alert.general.mainMenuItemGeneral',
        color: '#3f8cff',
        path: 'price.drop.alert.list',
        icon: 'regular-bell',
        parent: 'sw-catalogue',
        position: 120,
    }],
});
