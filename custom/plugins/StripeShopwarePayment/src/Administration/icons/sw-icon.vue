<script>

// eslint-disable-next-line import/no-webpack-loader-syntax
import * as StripeShopwarePaymentIconPluginSvg
  from '!svg-inline-loader?removeSVGTagAttrs=false!./stripe-shopware-payment-icon-plugin.svg';

export default {
  overrideFrom: 'sw-icon',

  watch: {
    name: {
      async handler(newName) {
        if (newName.indexOf('stripe-shopware-payment-icon') !== 0) {
          return;
        }

        // The watcher is called (for the first and in normal cases only time) before the beforeMount hook of
        // the component is executed. Unfortunately shopware resets the rendered svg string in this hook,
        // therefore need to wait for the next tick to ensure that our svg string gets rendered.
        //
        // eslint-disable-next-line max-len,vue/max-len
        // See https://github.com/shopware/platform/blob/acc85ca70ac44e22ebe1e6e2a370a6d92384083c/src/Administration/Resources/app/administration/src/app/component/base/sw-icon/index.js#L122
        //
        // This workaround will be removed as soon as sw-icon will support loading of third party icons
        // See https://github.com/pickware/shopware-plugins/issues/3924
        await this.$nextTick();
        switch (newName) {
          case 'stripe-shopware-payment-icon-plugin':
            this.iconSvgData = StripeShopwarePaymentIconPluginSvg;
            break;
          default:
            this.iconSvgData = '';
        }
      },
      immediate: true,
    },
  },
};
</script>
