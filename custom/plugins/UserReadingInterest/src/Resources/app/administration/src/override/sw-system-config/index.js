import template from './sw-system-config.html.twig';

Shopware.Component.override('sw-system-config', {
    template,

    computed: {
        showSalesChannelSwitch() {
            return this.domain !== 'UserReadingInterest.config';
        },
    },
});
