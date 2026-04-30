import template from './index.html.twig';

const { Criteria } = Shopware.Data;

Shopware.Component.register('price-drop-alert-list', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Shopware.Mixin.getByName('listing'),
    ],

    data() {
        return {
            items: null,
            isLoading: false,
            page: 1,
            limit: 25,
            total: 0,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('bookbytes_price_drop_alert');
        },

        columns() {
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
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));

            return criteria;
        },
    },

    created() {
        this.getList();
    },

    methods: {
        getList() {
            this.isLoading = true;

            return this.repository.search(this.criteria, Shopware.Context.api).then((result) => {
                this.items = result;
                this.total = result.total;
            }).finally(() => {
                this.isLoading = false;
            });
        },

        onPageChange({ page = 1, limit = 25 } = {}) {
            this.page = page;
            this.limit = limit;
            this.getList();
        },

        onColumnSort(column) {
            this.onSortColumn(column);
            this.getList();
        },
    },
});
