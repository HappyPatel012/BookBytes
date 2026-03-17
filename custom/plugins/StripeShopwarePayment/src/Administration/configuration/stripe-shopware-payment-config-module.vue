<script>
export default {
  name: 'stripe-shopware-payment-config-module',
  routePrefixName: 'stripe.shopware.payment',
  routePrefixPath: 'stripe/shopware/payment',
  type: 'plugin',
  color: '#9AA8B5',

  routes: {
    settings: {
      component: 'stripe-shopware-payment-config-page',
      path: 'settings',
      meta: {
        parentPath: 'sw.settings.index',
      },
    },
  },

  settingsItem: {
    group: 'plugins',
    to: 'stripe.shopware.payment.settings',
    iconComponent: 'stripe-shopware-payment-icon-plugin',
    backgroundEnabled: false,
    label: 'stripe-shopware-payment-config-module.stripe-shopware-payment',
  },
};
</script>

<i18n>
{
  "de-DE": {
    "stripe-shopware-payment-config-module": {
      "stripe-shopware-payment": "Stripe"
    }
  },
  "en-GB": {
    "stripe-shopware-payment-config-module": {
      "stripe-shopware-payment": "Stripe"
    }
  }
}
</i18n>
