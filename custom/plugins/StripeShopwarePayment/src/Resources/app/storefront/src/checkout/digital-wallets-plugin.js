import Plugin from 'src/plugin-system/plugin.class';

import $ from "jquery";
import {getOrderForm} from "../payment-selection/checkout-page-util";

export default class DigitalWalletsPlugin extends Plugin {
    get defaultOptions() {
        return {
            stripeApiVersion: undefined,
            stripePublicKey: '',
            salesChannelLocale: 'de',
            lineItems: [],
            countryCode: 'DE',
            currencyCode: 'EUR',
            currencyDecimalPrecision: 2,
            snippets: {},
        };
    }

    init() {
        if(!window.Stripe) {
            this.handleStripeError("error-stripe-js-loading")
            this.disableSubmitButton();
            return;
        }

        this.options = Object.assign(this.defaultOptions, this.options || {});

        this.stripeApiClient = window.Stripe(this.options.stripePublicKey, {
            apiVersion: this.options.stripeApiVersion,
            locale: this.options.salesChannelLocale.substr(0, 2),
        });

        this.getOrderForm().on('submit', this.onFormSubmission.bind(this));
        let vm = this;
        var _submit = getOrderForm().get(0).submit;
        getOrderForm().get(0).submit = function (event, doOriginalSubmit = false) {
            if(doOriginalSubmit === false) {
                vm.onFormSubmission.bind(vm)(event);
                return false;
            } else {
                _submit.call(this, event)
            }
        }

        this.createPaymentRequest();
    }

    async createPaymentRequest() {
        this.stripeShopwarePaymentRequest = this.stripeApiClient.paymentRequest({
            country: this.options.countryCode ? this.options.countryCode.toUpperCase() : 'DE',
            currency: this.options.currencyCode ? this.options.currencyCode.toLowerCase() : 'eur',
            total: {
                label: this.options.snippets.total,
                amount: this.getAmountInSmallestCurrencyUnit(this.options.totalAmount),
            },
            displayItems: this.getPaymentDisplayItems(),
            requestPayerName: true,
            requestPayerEmail: true,
        });

        // Handle "confirm" and "cancel" events of the browser payment interface popup
        this.stripeShopwarePaymentRequest.on('paymentmethod', this.onStripeShopwarePaymentMethodCreated.bind(this));
        this.stripeShopwarePaymentRequest.on('cancel', this.onStripeShopwarePaymentMethodCreationCancelled.bind(this));

        // Check for availability of the payment api
        this.browserPaymentApiAvailable = Boolean(await this.stripeShopwarePaymentRequest.canMakePayment());
        if (this.browserPaymentApiAvailable) {
            return;
        }

        // We have to manually check whether this site is served via HTTPS because even though Stripe.js checks the
        // used protocol and declines the payment if not served via HTTPS, only a generic 'not available' error message
        // is returned and the HTTPS warning is logged to the console. We however want to show a specific error message
        // that informs about the lack of security.
        if (!this.isSecureConnection()) {
            this.handleStripeError('connection-not-secure');
        } else {
            this.handleStripeError('payment-api-unavailable');
        }
        this.disableSubmitButton();
    }

    getPaymentDisplayItems() {
        const paymentDisplayItems = this.options.lineItems.map((item) => ({
            label: `${item.quantity}x ${item.label}`,
            amount: this.getAmountInSmallestCurrencyUnit(item.price.totalPrice),
        }));

        if (this.options.shippingCost) {
            paymentDisplayItems.push({
                label: this.options.snippets.shippingCost,
                amount: this.getAmountInSmallestCurrencyUnit(this.options.shippingCost),
            });
        }

        return paymentDisplayItems;
    }

    getAmountInSmallestCurrencyUnit(amount) {
        return Math.round(amount * Math.pow(10, 2))
    }

    isSecureConnection() {
        return window.location.protocol === 'https:';
    }

    onStripeShopwarePaymentMethodCreated(paymentResponse) {
        this.stripeShopwarePaymentMethodId = paymentResponse.paymentMethod.id;

        // Complete the browser's payment flow
        paymentResponse.complete('success');

        const orderForm = this.getOrderForm();
        $('input[name="stripeDigitalWalletsPaymentMethodId"]').remove();
        $('<input type="hidden" name="stripeDigitalWalletsPaymentMethodId"/>')
            .val(this.stripeShopwarePaymentMethodId)
            .appendTo(orderForm);

        orderForm.get(0).submit(null, true);
    }

    onStripeShopwarePaymentMethodCreationCancelled() {
        this.stripeShopwarePaymentMethodId = null;
        this.handleStripeError('payment-cancelled');
        this.resetSubmitButton();
    }

    /**
     * First validates the form and payment state and, if the main form can be submitted, does nothing further.
     * If however the main form cannot be submitted, because no Stripe payment method was created, a new Stripe
     * payment method is created using the payment api and saved in the form, before the submission is
     * triggered again.
     */
    onFormSubmission(event) {
        // Check if a Stripe payment method was already created and hence the form can be submitted
        if (this.stripeShopwarePaymentMethodId) {
            return;
        }

        // Prevent the form from being submitted until a new Stripe payment method is created
        if(event) {
            event.preventDefault();
        }

        this.stripeShopwarePaymentRequest.show();
    }

    disableSubmitButton() {
        this.getOrderSubmitButton().attr('disabled', 'disabled');
    }

    resetSubmitButton() {
        this.getOrderSubmitButton().removeAttr('disabled');
    }

    /**
     * Sets the given message in the general error box and scrolls the page to make it visible.
     */
    handleStripeError(errorCode) {
        const errorMessage = this.getTranslatedErrorMessage(errorCode);
        $('#stripe-shopware-payment-digital-wallets-errors')
            .removeClass('stripe-shopware-payment-digital-wallets-errors--hidden')
            .find('.alert-content')
            .html(`${this.options.snippets.error}: ${errorMessage}`);
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
    }

    getTranslatedErrorMessage(errorCode) {
        return this.options.snippets.errors[errorCode] || 'Unknown error';
    }

    /**
     * @returns {jQuery}
     */
    getOrderSubmitButton() {
        return $('#confirmFormSubmit');
    }

    /**
     * @return jQuery
     */
    getOrderForm() {
        return $('#confirmOrderForm');
    }
}
