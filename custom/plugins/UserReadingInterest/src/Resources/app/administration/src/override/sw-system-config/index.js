import template from './sw-system-config.html.twig';

Shopware.Component.override('sw-system-config', {
    template,

    computed: {
        showSalesChannelSwitch() {
            const routePath = typeof this.$route?.fullPath === 'string' ? this.$route.fullPath.toLowerCase() : '';
            if (routePath.includes('/sw/extension/config/userreadinginterest')) {
                return false;
            }

            const domain = typeof this.domain === 'string' ? this.domain.toLowerCase() : '';

            return !domain.includes('userreadinginterest.config');
        },
    },

    methods: {
        isInterestOptionsField(fieldName) {
            if (typeof fieldName !== 'string') {
                return false;
            }

            return fieldName === 'interestOptions'
                || fieldName === 'UserReadingInterest.config.interestOptions'
                || fieldName.endsWith('.interestOptions');
        },

        interestOptionsTextToTags(value) {
            if (typeof value !== 'string' || value.trim() === '') {
                return [];
            }

            return value
                .split(/\r\n|\r|\n/)
                .map((item) => item.trim())
                .filter((item) => item !== '');
        },

        interestOptionsTagsToText(tags) {
            if (!Array.isArray(tags) || tags.length === 0) {
                return '';
            }

            const normalized = tags
                .map((item) => String(item).trim())
                .filter((item) => item !== '');

            return normalized.join('\n');
        },

        onInterestOptionsTagsChange(tags, props) {
            props.updateCurrentValue(this.interestOptionsTagsToText(tags));
        },
    },
});
