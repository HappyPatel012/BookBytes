import template from './index.html.twig';

Shopware.Component.register('user-reading-interest-settings', {
    template,

    inject: ['systemConfigApiService'],

    mixins: [
        Shopware.Mixin.getByName('notification'),
    ],

    data() {
        return {
            isLoading: false,
            isSaving: false,
            allowedSalesChannels: [],
        };
    },

    created() {
        this.loadConfig();
    },

    methods: {
        loadConfig() {
            this.isLoading = true;

            return this.systemConfigApiService.getValues('UserReadingInterest.config').then((values) => {
                this.allowedSalesChannels = values['UserReadingInterest.config.enabledSalesChannels'] || [];
            }).catch(() => {
                this.createNotificationError({ message: 'Could not load settings.' });
            }).finally(() => {
                this.isLoading = false;
            });
        },

        onSave() {
            this.isSaving = true;

            return this.systemConfigApiService.saveValues({
                'UserReadingInterest.config.enabledSalesChannels': this.allowedSalesChannels,
            }).then(() => {
                this.createNotificationSuccess({ message: 'Settings saved.' });
            }).catch(() => {
                this.createNotificationError({ message: 'Could not save settings.' });
            }).finally(() => {
                this.isSaving = false;
            });
        },
    },
});
