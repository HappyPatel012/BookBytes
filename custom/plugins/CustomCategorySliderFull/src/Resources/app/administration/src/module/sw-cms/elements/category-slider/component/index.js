import template from './index.html.twig';

const { Mixin } = Shopware;

Shopware.Component.register('sw-cms-el-custom-category-slider-full', {
    template,

    mixins: [
        Mixin.getByName('cms-element'),
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('custom-category-slider-full');
        },
    },
});
