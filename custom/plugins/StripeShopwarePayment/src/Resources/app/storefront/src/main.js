// Regenerator runtime is required to enable async/await
import 'regenerator-runtime/runtime.js';

import DigitalWalletsPlugin from './checkout/digital-wallets-plugin.js';
import CardSelectionPlugin from './payment-selection/card-selection-plugin.js';
import SepaBankAccountSelectionPlugin from './payment-selection/sepa-bank-account-selection-plugin.js';

const PluginManager = window.PluginManager;
PluginManager.register(
    'StripeShopwarePaymentCardSelection',
    CardSelectionPlugin,
    '[data-stripe-shopware-payment-card-selection]',
);
PluginManager.register(
    'StripeShopwarePaymentDigitalWallets',
    DigitalWalletsPlugin,
    '[data-stripe-shopware-payment-digital-wallets]',
);
PluginManager.register(
    'StripeShopwarePaymentSepaBankAccountSelection',
    SepaBankAccountSelectionPlugin,
    '[data-stripe-shopware-payment-sepa-bank-account-selection]',
);
