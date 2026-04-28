import template from './sw-extension-config.html.twig';

Shopware.Component.override('sw-extension-config', {
    template,

    computed: {
        isUserReadingInterestConfig() {
            return this.namespace === 'UserReadingInterest';
        },
    },
});
