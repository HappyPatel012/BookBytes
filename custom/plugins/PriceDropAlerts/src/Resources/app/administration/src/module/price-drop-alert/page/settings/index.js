import template from './index.html.twig';

Shopware.Component.register('price-drop-alert-settings', {
    template,

    mixins: [
        Shopware.Mixin.getByName('notification'),
    ],

    methods: {
        onCancel() {
            this.$router.push({
                name: 'price.drop.alert.list',
                query: {
                    limit: 25,
                    page: 1,
                    sortBy: 'createdAt',
                    sortDirection: 'DESC',
                    naturalSorting: false,
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
