import template from './index.html.twig';
import './index.scss';

const { Criteria } = Shopware.Data;

Shopware.Component.register('user-reading-interest-list', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('listing'),
    ],

    data() {
        return {
            items: null,
            isLoading: false,
            sortBy: 'createdAt',
            naturalSorting: false,
            sortDirection: 'DESC',
            searchTerm: '',
            showModal: false,
            currentItem: this.createEmptyItem(),
            isSaving: false,
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('user_reading_interest');
        },

        columns() {
            return [
                { property: 'name', label: 'Interest', primary: true, allowResize: true },
                { property: 'description', label: 'Description', allowResize: true },
                { property: 'customer.firstName', label: 'Customer', allowResize: true },
                { property: 'createdAt', label: 'Created', allowResize: true },
            ];
        },

        defaultCriteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.setTerm(this.searchTerm);
            criteria.addAssociation('customer');
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));

            return criteria;
        },

        customerCriteria() {
            return new Criteria(1, 25);
        },
    },

    created() {
        this.getList();
    },

    methods: {
        createEmptyItem() {
            return {
                id: null,
                customerId: null,
                name: '',
                description: '',
            };
        },

        getList() {
            this.isLoading = true;

            return this.repository.search(this.defaultCriteria, Shopware.Context.api).then((result) => {
                this.items = result;
                this.total = result.total;
            }).finally(() => {
                this.isLoading = false;
            });
        },

        onPageChange() {
            this.getList();
        },

        onColumnSort(column) {
            this.onSortColumn(column);
            this.getList();
        },

        onSearch(term) {
            this.searchTerm = term;
            this.page = 1;
            this.getList();
        },

        onCreateNew() {
            this.currentItem = this.createEmptyItem();
            this.showModal = true;
        },

        onEdit(item) {
            this.currentItem = {
                id: item.id,
                customerId: item.customerId,
                name: item.name,
                description: item.description || '',
            };
            this.showModal = true;
        },

        onCloseModal() {
            this.showModal = false;
            this.currentItem = this.createEmptyItem();
        },

        onSave() {
            if (!this.currentItem.customerId || !this.currentItem.name.trim()) {
                this.createNotificationError({ message: 'Customer and interest name are required.' });
                return;
            }

            this.isSaving = true;

            const payload = {
                id: this.currentItem.id,
                customerId: this.currentItem.customerId,
                name: this.currentItem.name.trim(),
                description: this.currentItem.description ? this.currentItem.description.trim() : null,
            };

            const entity = this.currentItem.id
                ? { ...this.currentItem }
                : this.repository.create(Shopware.Context.api);

            Object.assign(entity, payload);

            this.repository.save(entity, Shopware.Context.api).then(() => {
                this.createNotificationSuccess({ message: 'Reading interest saved.' });
                this.onCloseModal();
                this.getList();
            }).catch(() => {
                this.createNotificationError({ message: 'Could not save reading interest.' });
            }).finally(() => {
                this.isSaving = false;
            });
        },

        onDelete(item) {
            return this.repository.delete(item.id, Shopware.Context.api).then(() => {
                this.createNotificationSuccess({ message: 'Reading interest deleted.' });
                this.getList();
            }).catch(() => {
                this.createNotificationError({ message: 'Could not delete reading interest.' });
            });
        },
    },
});
