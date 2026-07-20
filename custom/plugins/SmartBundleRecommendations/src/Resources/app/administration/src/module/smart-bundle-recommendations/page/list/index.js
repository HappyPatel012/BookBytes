import template from './index.html.twig';

const { Criteria } = Shopware.Data;

Shopware.Component.register('smart-bundle-recommendations-list', {
    template,
    inject: ['repositoryFactory'],
    data() {
        return {
            rules: [],
            isLoading: false,
        };
    },
    computed: {
        ruleRepository() {
            return this.repositoryFactory.create('bookbytes_bundle_rule');
        },
    },
    created() {
        this.load();
    },
    methods: {
        load() {
            this.isLoading = true;
            this.ruleRepository.search(new Criteria(1, 25), Shopware.Context.api).then((result) => {
                this.rules = result;
                this.isLoading = false;
            });
        },
    },
});
