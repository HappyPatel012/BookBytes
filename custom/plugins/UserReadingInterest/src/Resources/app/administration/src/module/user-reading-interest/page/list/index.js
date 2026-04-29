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
            isExporting: false,
            page: 1,
            limit: 10,
            total: 0,
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
            return new Criteria(1, 10);
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

        onPageChange({ page = 1, limit = 10 } = {}) {
            this.page = page;
            this.limit = limit;
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

        createExportCriteria(page, limit) {
            const criteria = new Criteria(page, limit);
            criteria.setTerm(this.searchTerm);
            criteria.addAssociation('customer');
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));

            return criteria;
        },

        async fetchAllInterestsForExport() {
            const pageSize = 500;
            let page = 1;
            let total = 0;
            const allItems = [];

            do {
                const result = await this.repository.search(
                    this.createExportCriteria(page, pageSize),
                    Shopware.Context.api
                );

                total = result.total || 0;

                result.forEach((item) => {
                    allItems.push(item);
                });

                page += 1;

                if (result.length < pageSize) {
                    break;
                }
            } while (allItems.length < total);

            return allItems;
        },

        escapeCsvValue(value) {
            const stringValue = value === null || value === undefined ? '' : String(value);
            const escaped = stringValue.replace(/"/g, '""');

            if (/[",\n\r]/.test(escaped)) {
                return `"${escaped}"`;
            }

            return escaped;
        },

        formatDate(value) {
            if (!value) {
                return '';
            }

            try {
                return new Date(value).toISOString();
            } catch (e) {
                return String(value);
            }
        },

        convertRowsToCsv(rows) {
            const headers = [
                'ID',
                'Interest Name',
                'Description',
                'Customer First Name',
                'Customer Last Name',
                'Customer Email',
                'Created At',
                'Updated At',
            ];

            const lines = [headers.map((header) => this.escapeCsvValue(header)).join(',')];

            rows.forEach((item) => {
                lines.push([
                    this.escapeCsvValue(item.id),
                    this.escapeCsvValue(item.name),
                    this.escapeCsvValue(item.description || ''),
                    this.escapeCsvValue(item.customer?.firstName || ''),
                    this.escapeCsvValue(item.customer?.lastName || ''),
                    this.escapeCsvValue(item.customer?.email || ''),
                    this.escapeCsvValue(this.formatDate(item.createdAt)),
                    this.escapeCsvValue(this.formatDate(item.updatedAt)),
                ].join(','));
            });

            return lines.join('\r\n');
        },

        downloadCsv(content) {
            const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');

            link.href = url;
            link.download = `reading-interests-${timestamp}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        },

        async onExportCsv() {
            this.isExporting = true;

            try {
                const rows = await this.fetchAllInterestsForExport();
                const csv = this.convertRowsToCsv(rows);
                this.downloadCsv(csv);
                this.createNotificationSuccess({ message: `Exported ${rows.length} reading interests.` });
            } catch (e) {
                this.createNotificationError({ message: 'Could not export reading interests CSV.' });
            } finally {
                this.isExporting = false;
            }
        },
    },
});
