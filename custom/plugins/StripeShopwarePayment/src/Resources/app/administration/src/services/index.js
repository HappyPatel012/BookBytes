import StripeShopwarePaymentConfigApiService from './stripe-shopware-payment-config-api-service.js';
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