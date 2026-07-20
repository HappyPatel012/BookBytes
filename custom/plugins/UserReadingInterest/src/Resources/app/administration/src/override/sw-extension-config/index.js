import template from './sw-extension-config.html.twig';

Shopware.Component.override('sw-extension-config', {
    template,

    computed: {
        isUserReadingInterestConfig() {
            const namespaceCandidates = [
                this.namespace,
                this.$route?.params?.namespace,
                this.$route?.params?.extensionName,
            ]
                .filter((value) => typeof value === 'string')
                .map((value) => value.toLowerCase());

            return namespaceCandidates.some((value) => value.includes('userreadinginterest'));
        },
    },

    created() {
        // Prevent direct access to the Shopware "extension config" page for this plugin.
        // We redirect to the plugin's own admin module settings instead.
        if (this.isUserReadingInterestConfig) {
            this.$router?.replace({ name: 'user.reading.interest.settings' });
        }
    },
});
