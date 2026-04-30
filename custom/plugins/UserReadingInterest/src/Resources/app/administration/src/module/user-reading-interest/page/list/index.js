import template from './index.html.twig';
import './index.scss';

const { Criteria } = Shopware.Data;

Shopware.Component.register('user-reading-interest-list', {
    template,

    inject: ['repositoryFactory', 'systemConfigApiService'],

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
            filterTerm: '',
            selectedAttribute: 'all',
            interestOptions: [],
            showModal: false,
            currentItem: this.createEmptyItem(),
            isSaving: false,
            selectedItems: {},
            isBulkDeleting: false,
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('user_reading_interest');
        },

        categoryRepository() {
            return this.repositoryFactory.create('category');
        },

        columns() {
            return [
                { property: 'name', label: 'Interest', primary: true, allowResize: true },
                { property: 'description', label: 'Description', allowResize: true },
                { property: 'customer.firstName', label: 'Customer', allowResize: true },
                { property: 'createdAt', label: 'Created', allowResize: true },
                { property: 'rowActions', label: 'Action', allowResize: false, sortable: false },
            ];
        },

        attributeOptions() {
            return [
                { label: 'All fields', value: 'all' },
                { label: 'Interest', value: 'name' },
                { label: 'Description', value: 'description' },
                { label: 'Customer first name', value: 'customer.firstName' },
                { label: 'Customer last name', value: 'customer.lastName' },
                { label: 'Customer email', value: 'customer.email' },
            ];
        },

        defaultCriteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.addAssociation('customer');
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));
            const trimmedSearch = String(this.searchTerm || '').trim();

            if (trimmedSearch) {
                if (this.selectedAttribute === 'all') {
                    criteria.setTerm(trimmedSearch);
                } else {
                    criteria.addFilter(Criteria.contains(this.selectedAttribute, trimmedSearch));
                }
            }

            return criteria;
        },

        customerCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addSorting(Criteria.sort('firstName', 'ASC'));

            return criteria;
        },

        interestSelectOptions() {
            const options = this.interestOptions.map((value) => ({ label: value, value }));

            if (
                this.currentItem.name
                && !this.interestOptions.includes(this.currentItem.name)
            ) {
                options.unshift({
                    label: `${this.currentItem.name} (current)`,
                    value: this.currentItem.name,
                });
            }

            return options;
        },

        selectedIds() {
            return Object.keys(this.selectedItems || {});
        },

        hasSelection() {
            return this.selectedIds.length > 0;
        },

    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent() {
            this.applyDefaultPagination();
            await this.loadInterestOptions();
            await this.getList();
        },

        applyDefaultPagination() {
            const queryLimit = Number(this.$route?.query?.limit);

            if (Number.isNaN(queryLimit) || queryLimit !== 10) {
                this.limit = 10;
                this.page = 1;

                this.$router.replace({
                    query: {
                        ...this.$route.query,
                        limit: 10,
                        page: 1,
                    },
                });
                return;
            }

            this.limit = 10;
        },

        createEmptyItem() {
            return {
                id: null,
                customerId: null,
                name: '',
                description: '',
            };
        },

        normalizeDescriptionValue(entity) {
            const rawValue = entity?.description ?? entity?.translated?.description ?? '';
            return String(rawValue || '');
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
            this.filterTerm = term;
            this.page = 1;
            this.getList();
        },

        onApplyFilters() {
            this.searchTerm = String(this.filterTerm || '').trim();
            this.page = 1;
            this.getList();
        },

        onResetFilters() {
            this.filterTerm = '';
            this.searchTerm = '';
            this.selectedAttribute = 'all';
            this.page = 1;
            this.getList();
        },

        onSelectionChange(selection) {
            this.selectedItems = selection || {};
        },

        clearSelection() {
            this.selectedItems = {};

            if (this.$refs.readingInterestListing && typeof this.$refs.readingInterestListing.resetSelection === 'function') {
                this.$refs.readingInterestListing.resetSelection();
            }
        },

        async onBulkDeleteSelected() {
            if (!this.hasSelection || this.isBulkDeleting) {
                return;
            }

            this.isBulkDeleting = true;

            try {
                await Promise.all(this.selectedIds.map((id) => this.repository.delete(id, Shopware.Context.api)));
                this.createNotificationSuccess({ message: `${this.selectedIds.length} reading interest(s) deleted.` });
                this.clearSelection();
                await this.getList();
            } catch (e) {
                this.createNotificationError({ message: 'Could not delete selected reading interests.' });
            } finally {
                this.isBulkDeleting = false;
            }
        },

        onCreateNew() {
            this.currentItem = this.createEmptyItem();
            this.showModal = true;
        },

        onEdit(item) {
            this.isLoading = true;

            return this.repository.get(item.id, Shopware.Context.api).then((entity) => {
                this.currentItem = {
                    id: entity.id,
                    customerId: entity.customerId,
                    name: entity.name || '',
                    description: this.normalizeDescriptionValue(entity),
                };
                this.showModal = true;
            }).catch(() => {
                this.createNotificationError({ message: 'Could not load reading interest for editing.' });
            }).finally(() => {
                this.isLoading = false;
            });
        },

        onCloseModal() {
            this.showModal = false;
            this.currentItem = this.createEmptyItem();
        },

        normalizeManualInterestOptions(configuredOptions) {
            if (Array.isArray(configuredOptions)) {
                return configuredOptions
                    .map((value) => String(value || '').trim())
                    .filter((value) => value !== '');
            }

            const manual = String(configuredOptions || '').trim();
            if (!manual) {
                return [];
            }

            return manual
                .split(/\r\n|\r|\n/)
                .map((value) => String(value || '').trim())
                .filter((value) => value !== '');
        },

        async loadInterestOptions() {
            try {
                const config = await this.systemConfigApiService.getValues('UserReadingInterest.config');
                const manualValues = this.normalizeManualInterestOptions(
                    config['UserReadingInterest.config.interestOptions']
                );

                const manualCategoryIds = Array.isArray(config['UserReadingInterest.config.manualCategories'])
                    ? config['UserReadingInterest.config.manualCategories']
                    : [];

                const categoryNames = [];
                const validCategoryIds = manualCategoryIds.filter((id) => typeof id === 'string' && id.length > 0);

                if (validCategoryIds.length > 0) {
                    const criteria = new Criteria(1, 500);
                    criteria.addFilter(Criteria.equalsAny('id', validCategoryIds));
                    criteria.addSorting(Criteria.sort('name', 'ASC'));

                    const categories = await this.categoryRepository.search(criteria, Shopware.Context.api);
                    categories.forEach((category) => {
                        const name = String(category?.translated?.name || category?.name || '').trim();
                        if (name) {
                            categoryNames.push(name);
                        }
                    });
                }

                this.interestOptions = [...new Set([...manualValues, ...categoryNames])]
                    .sort((a, b) => a.localeCompare(b, undefined, { sensitivity: 'base', numeric: true }));
            } catch (e) {
                this.interestOptions = [];
                this.createNotificationError({ message: 'Could not load configured interest options.' });
            }
        },

        async onSave() {
            if (!this.currentItem.customerId || !this.currentItem.name.trim()) {
                this.createNotificationError({ message: 'Customer and interest name are required.' });
                return;
            }

            this.isSaving = true;
            const normalizedName = this.currentItem.name.trim();

            const payload = {
                id: this.currentItem.id,
                customerId: this.currentItem.customerId,
                name: normalizedName,
                description: this.currentItem.description ? this.currentItem.description.trim() : null,
            };

            try {
                const duplicateExists = await this.hasDuplicateInterest(payload.customerId, normalizedName, payload.id);

                if (duplicateExists) {
                    this.createNotificationError({
                        message: 'This user already has the same category/interest. Duplicate entries are not allowed.',
                    });
                    return;
                }

                const entity = this.currentItem.id
                    ? await this.repository.get(this.currentItem.id, Shopware.Context.api)
                    : this.repository.create(Shopware.Context.api);

                Object.assign(entity, payload);
                await this.repository.save(entity, Shopware.Context.api);
                this.createNotificationSuccess({ message: 'Reading interest saved.' });
                this.onCloseModal();
                await this.getList();
            } catch (e) {
                this.createNotificationError({ message: 'Could not save reading interest.' });
            } finally {
                this.isSaving = false;
            }
        },

        async hasDuplicateInterest(customerId, interestName, currentId = null) {
            const criteria = new Criteria(1, 500);
            criteria.addFilter(Criteria.equals('customerId', customerId));

            const existing = await this.repository.search(criteria, Shopware.Context.api);
            const normalizedTarget = String(interestName || '').trim().toLowerCase();

            return existing.some((item) => {
                const itemId = item?.id || null;
                const itemName = String(item?.name || '').trim().toLowerCase();

                if (currentId && itemId === currentId) {
                    return false;
                }

                return itemName === normalizedTarget;
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
