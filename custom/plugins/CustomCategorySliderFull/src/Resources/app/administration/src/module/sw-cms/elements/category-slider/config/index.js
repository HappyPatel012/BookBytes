import template from './index.html.twig';

const { Mixin } = Shopware;

Shopware.Component.register('sw-cms-el-config-custom-category-slider-full', {
    template,

    mixins: [
        Mixin.getByName('cms-element'),
    ],

    created() {
        this.createdComponent();
    },

    computed: {
        titleValue: {
            get() {
                return this.element?.config?.title?.value || '';
            },
            set(value) {
                this.element.config.title.value = value;
            },
        },
    },

    methods: {
        createdComponent() {
            this.initElementConfig('custom-category-slider-full');
        },

        onTitleInput(value) {
            this.titleValue = value;
            this.$emit('element-update', this.element);
        },
    },
});
