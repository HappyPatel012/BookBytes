import template from './index.html.twig';
import './index.scss';

const { Criteria } = Shopware.Data;

Shopware.Component.register('price-drop-alert-list', {
    template,

    inject: ['repositoryFactory', 'systemConfigApiService'],

    mixins: [
        Shopware.Mixin.getByName('listing'),
        Shopware.Mixin.getByName('notification'),
    ],

    data() {
        return {
            items: null,
            isLoading: false,
            alertType: 'priceDrop',
            page: 1,
            limit: 10,
            total: 0,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            naturalSorting: false,
            showProductPrice: true,
            autoCreateBackInStockFromWishlist: false,
            isSavingConfig: false,
        };
    },

    computed: {
        currentEntityName() {
            return this.alertType === 'backInStock'
                ? 'bookbytes_back_in_stock_alert'
                : 'bookbytes_price_drop_alert';
        },

        repository() {
            return this.repositoryFactory.create(this.currentEntityName);
        },

        pageTitle() {
            return this.alertType === 'backInStock' ? 'Back-in-Stock Alerts' : 'Price Drop Alerts';
        },

        alertTypeOptions() {
            return [
                { value: 'priceDrop', label: 'Price Drop Alerts' },
                { value: 'backInStock', label: 'Back-in-Stock Alerts' },
            ];
        },

        columns() {
            if (this.alertType === 'backInStock') {
                return [
                    { property: 'customer.email', label: 'Customer email', allowResize: true },
                    { property: 'product.name', label: 'Product', allowResize: true },
                    { property: 'lastKnownStock', label: 'Last known stock', allowResize: true },
                    { property: 'active', label: 'Active', allowResize: true },
                    { property: 'lastNotifiedAt', label: 'Last notified', allowResize: true },
                    { property: 'createdAt', label: 'Created at', allowResize: true },
                ];
            }

            return [
                { property: 'customer.email', label: 'Customer email', allowResize: true },
                { property: 'product.name', label: 'Product', allowResize: true },
                { property: 'lastKnownGrossPrice', label: 'Last known gross price', allowResize: true },
                { property: 'active', label: 'Active', allowResize: true },
                { property: 'lastNotifiedAt', label: 'Last notified', allowResize: true },
                { property: 'createdAt', label: 'Created at', allowResize: true },
            ];
        },

        criteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addAssociation('customer');
            criteria.addAssociation('product');

            if (this.alertType === 'backInStock') {
                criteria.addFilter(Criteria.equals('product.stock', 0));
            }

            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));

            return criteria;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent() {
            this.applyPaginationFromRoute();
            await this.loadSystemConfig();
            await this.getList();
        },

        applyPaginationFromRoute() {
            const query = this.$route?.query || {};
            const allowedAlertTypes = ['priceDrop', 'backInStock'];
            const queryLimit = Number(query.limit);
            const queryPage = Number(query.page);
            const allowedSortBy = ['createdAt', 'lastNotifiedAt', 'lastKnownGrossPrice', 'lastKnownStock', 'active'];
            const normalizedSortBy = allowedSortBy.includes(query.sortBy) ? query.sortBy : 'createdAt';
            const normalizedSortDirection = query.sortDirection === 'ASC' ? 'ASC' : 'DESC';
            const normalizedNaturalSorting = String(query.naturalSorting) === 'true';
            const normalizedAlertType = allowedAlertTypes.includes(query.alertType) ? query.alertType : 'priceDrop';

            this.alertType = normalizedAlertType;

            if (Number.isNaN(queryLimit) || queryLimit !== 10) {
                this.limit = 10;
                this.page = 1;
                this.sortBy = normalizedSortBy;
                this.sortDirection = normalizedSortDirection;
                this.naturalSorting = normalizedNaturalSorting;

                this.$router.replace({
                    query: {
                        ...query,
                        limit: 10,
                        page: 1,
                        sortBy: this.sortBy,
                        sortDirection: this.sortDirection,
                        naturalSorting: this.naturalSorting,
                        alertType: this.alertType,
                    },
                });
                return;
            }

            this.limit = 10;
            this.page = Number.isNaN(queryPage) || queryPage <= 0 ? 1 : queryPage;
            this.sortBy = normalizedSortBy;
            this.sortDirection = normalizedSortDirection;
            this.naturalSorting = normalizedNaturalSorting;

            if (
                String(query.limit) !== String(this.limit)
                || String(query.page) !== String(this.page)
                || query.sortBy !== this.sortBy
                || query.sortDirection !== this.sortDirection
                || String(query.naturalSorting) !== String(this.naturalSorting)
                || query.alertType !== this.alertType
            ) {
                this.$router.replace({
                    query: {
                        ...query,
                        limit: this.limit,
                        page: this.page,
                        sortBy: this.sortBy,
                        sortDirection: this.sortDirection,
                        naturalSorting: this.naturalSorting,
                        alertType: this.alertType,
                    },
                });
            }
        },

        syncPaginationQuery() {
            this.$router.replace({
                query: {
                    ...this.$route.query,
                    limit: this.limit,
                    page: this.page,
                    sortBy: this.sortBy,
                    sortDirection: this.sortDirection,
                    naturalSorting: this.naturalSorting,
                    alertType: this.alertType,
                },
            });
        },

        onChangeAlertType(value) {
            this.alertType = value;
            this.page = 1;
            this.sortBy = 'createdAt';
            this.sortDirection = 'DESC';
            this.syncPaginationQuery();
            this.getList();
        },

        loadSystemConfig() {
            return this.systemConfigApiService.getValues('PriceDropAlerts.config').then((values) => {
                this.showProductPrice = values['PriceDropAlerts.config.showProductPrice'] !== false;
                this.autoCreateBackInStockFromWishlist = values['PriceDropAlerts.config.autoCreateBackInStockFromWishlist'] === true;
            }).catch(() => {
                this.createNotificationError({ message: 'Could not load alert settings.' });
            });
        },

        onTogglePriceVisibility(value) {
            this.isSavingConfig = true;

            return this.systemConfigApiService.saveValues({
                'PriceDropAlerts.config.showProductPrice': value,
            }).then(() => {
                this.showProductPrice = value;
                this.createNotificationSuccess({ message: 'Price visibility updated.' });
            }).catch(() => {
                this.showProductPrice = !value;
                this.createNotificationError({ message: 'Could not update price visibility.' });
            }).finally(() => {
                this.isSavingConfig = false;
            });
        },

        onToggleBackInStockAutoCreate(value) {
            this.isSavingConfig = true;

            return this.systemConfigApiService.saveValues({
                'PriceDropAlerts.config.autoCreateBackInStockFromWishlist': value,
            }).then(() => {
                this.autoCreateBackInStockFromWishlist = value;
                this.createNotificationSuccess({ message: 'Back-in-stock auto-create updated.' });
            }).catch(() => {
                this.autoCreateBackInStockFromWishlist = !value;
                this.createNotificationError({ message: 'Could not update back-in-stock auto-create setting.' });
            }).finally(() => {
                this.isSavingConfig = false;
            });
        },

        getList() {
            this.isLoading = true;

            return this.repository.search(this.criteria, Shopware.Context.api).then((result) => {
                this.items = result;
                this.total = result.total;
            }).finally(() => {
                this.isLoading = false;
            });
        },

        onPageChange({ page = 1, limit = 10 } = {}) {
            this.page = page;
            this.limit = limit;
            this.syncPaginationQuery();
            this.getList();
        },

        onColumnSort(column) {
            this.onSortColumn(column);
            this.syncPaginationQuery();
            this.getList();
        },
    },
});
