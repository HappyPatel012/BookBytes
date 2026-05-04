import template from './index.html.twig';

Shopware.Component.register('user-reading-interest-settings', {
    template,

    mixins: [
        Shopware.Mixin.getByName('notification'),
    ],

    methods: {
        onCancel() {
            this.$router.push({
                name: 'user.reading.interest.index',
                query: {
                    limit: 10,
                    page: 1,
                },
            });
        },

        onSave() {
            this.$refs.systemConfig.saveAll().then(() => {
                this.createNotificationSuccess({ message: 'Settings saved.' });
            }).catch(() => {
                this.createNotificationError({ message: 'Could not save settings.' });
            });
        },
    },
});
