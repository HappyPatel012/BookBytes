import './page/list';

Shopware.Module.register('smart-bundle-recommendations', {
    type: 'plugin',
    name: 'SmartBundleRecommendations',
    title: 'smart-bundle-recommendations.general.mainMenuItemGeneral',
    description: 'Manage recommendation rules and preview output',
    color: '#2b6cb0',
    icon: 'regular-shopping-basket',
    routes: {
        list: {
            component: 'smart-bundle-recommendations-list',
            path: 'list',
        },
    },
    navigation: [{
        id: 'smart-bundle-recommendations',
        label: 'smart-bundle-recommendations.general.mainMenuItemGeneral',
        color: '#2b6cb0',
        path: 'smart.bundle.recommendations.list',
        icon: 'regular-shopping-basket',
        parent: 'sw-marketing',
        position: 100,
    }],
});
