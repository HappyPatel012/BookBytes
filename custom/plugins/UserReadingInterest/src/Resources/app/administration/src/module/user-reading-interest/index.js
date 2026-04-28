import './page/list';
import './page/settings';

Shopware.Module.register('user-reading-interest', {
    type: 'plugin',
    name: 'UserReadingInterest',
    title: 'Reading Interests',
    description: 'Manage customer reading interests',
    color: '#4a8f6a',
    icon: 'regular-book',

    routes: {
        index: {
            component: 'user-reading-interest-list',
            path: 'index',
        },
        settings: {
            component: 'user-reading-interest-settings',
            path: 'settings',
        },
    },

    navigation: [{
        id: 'user-reading-interest',
        label: 'Reading Interests',
        color: '#4a8f6a',
        path: 'user.reading.interest.index',
        icon: 'regular-book',
        parent: 'sw-customer',
        position: 120,
    }],
});
