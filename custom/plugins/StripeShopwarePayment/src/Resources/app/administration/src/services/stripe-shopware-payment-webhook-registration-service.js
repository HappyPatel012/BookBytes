const ApiService = Shopware.Classes.ApiService;

const API_ENDPOINT = 'stripe-payment';

class StripeShopwarePaymentWebhookRegistrationService extends ApiService {
    constructor(httpClient, loginService) {
        super(httpClient, loginService, API_ENDPOINT);
    }

    async registerWebhook(salesChannelId) {
        const headers = this.getBasicHeaders();

        const response = await this.httpClient.put(
            `_action/${this.getApiBasePath()}/register-webhook`,
            { salesChannelId },
            { headers },
        );

        return ApiService.handleResponse(response);
    }
}

export default StripeShopwarePaymentWebhookRegistrationService;
