// We need to import the components via the asterisk syntax so that we also get the template
import { componentRegistrator } from '../component-registrator.js';
import StripeShopwarePaymentConfigApiService from './stripe-shopware-payment-config-api-service.js';
import StripeShopwarePaymentConfigModule from './stripe-shopware-payment-config-module.vue';
import * as StripeShopwarePaymentConfigPage from './stripe-shopware-payment-config-page.vue';
import StripeShopwarePaymentWebhookRegistrationService from './stripe-shopware-payment-webhook-registration-service.js';

const { Application } = Shopware;

Application.addServiceProvider('stripeShopwarePaymentWebhookRegistrationService', (container) => {
    const initContainer = Application.getContainer('init');

    return new StripeShopwarePaymentWebhookRegistrationService(initContainer.httpClient, container.loginService);
});
Application.addServiceProvider('stripeShopwarePaymentConfigApiService', (container) => {
    const initContainer = Application.getContainer('init');

    return new StripeShopwarePaymentConfigApiService(initContainer.httpClient, container.loginService);
});

componentRegistrator.registerComponent(StripeShopwarePaymentConfigPage);
componentRegistrator.registerModule(StripeShopwarePaymentConfigModule);
