import { loadStripe } from '@stripe/stripe-js';
import 'regenerator-runtime/runtime.js';

const ApiService = Shopware.Classes.ApiService;

const API_ENDPOINT = 'stripe-payment';

class StripeShopwarePaymentConfigApiService extends ApiService {
    constructor(httpClient, loginService) {
        super(httpClient, loginService, API_ENDPOINT);
    }

    async validateSecretKey(stripeSecretKey) {
        const headers = this.getBasicHeaders();

        const response = await this.httpClient.post(
            `_action/${this.getApiBasePath()}/validate-secret-key`,
            { stripeSecretKey },
            { headers },
        );

        return ApiService.handleResponse(response);
    }

    async validatePublishableKey(stripePublishableKey) {
        const stripeApiClient = await loadStripe(stripePublishableKey);
        try {
            const result = await stripeApiClient.createToken('pii', {
                personal_id_number: 'test',
            });

            return Boolean(result.token);
        } catch (error) {
            return false;
        }
    }

    async updateStripeAccountCountry({ salesChannelId }) {
        const headers = this.getBasicHeaders();

        const response = await this.httpClient.post(
            `_action/${this.getApiBasePath()}/update-stripe-account-country`,
            { salesChannelId },
            { headers },
        );

        return ApiService.handleResponse(response);
    }
}

export default StripeShopwarePaymentConfigApiService;
