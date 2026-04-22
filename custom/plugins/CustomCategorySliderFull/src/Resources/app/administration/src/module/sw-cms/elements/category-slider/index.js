import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'custom-category-slider-full',
    label: 'Custom Category Slider Full',
    component: 'sw-cms-el-custom-category-slider-full',
    configComponent: 'sw-cms-el-config-custom-category-slider-full',
    previewComponent: 'sw-cms-el-preview-custom-category-slider-full',
    defaultConfig: {
        title: {
            source: 'static',
            value: 'Browse Categories',
        },
    },
});
