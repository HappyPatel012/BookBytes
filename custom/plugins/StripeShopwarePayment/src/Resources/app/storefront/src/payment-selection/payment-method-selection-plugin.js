import Plugin  from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

import { getOrderForm, getPaymentMethodForm, resetOrderFormSubmitButton, getOrderFormSubmitButton } from './checkout-page-util.js';

import $ from "jquery";

export default class PaymentMethodSelectionPlugin extends Plugin {
    init() {
        this.stripeUiElements = [];
        this.inputFields = [];
        this.invalidFields = {};
        this.stripeApiErrors = {};
        this.genericErrors = {};

        if(!window.Stripe) {
            this.handleGenericError("error-stripe-js-loading")
            getOrderFormSubmitButton().prop('disabled', 'disabled');
            return;
        }

        this.options = Object.assign(this.defaultOptions || {}, this.options || {});
        this.httpClient = new HttpClient();

        this.stripeApiClient = window.Stripe(this.options.stripePublicKey, {
            apiVersion: this.options.stripeApiVersion,
            locale: this.options.salesChannelLocale.substr(0, 2),
        });
        this.stripeUiElementManager = this.stripeApiClient.elements();
    }

    /**
     * Mounts the Stripe elements to the stripe payment method form and adds the required listeners to detect
     * validation errors and form submissions.
     */
    mountForm() {
        this.mountStripeUiElements();
        this.addFormListeners();
    }

    showForm() {
        this.getForm().show();
        this.inputFields.forEach((inputField) => inputField.prop('disabled', false));
    }

    addFormListeners() {
        getOrderForm().on('submit', this.onFormSubmission.bind(this));
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
        this.inputFields.forEach((inputField) => {
            inputField.data('oldVal', inputField.val());
            inputField.on('propertychange keyup input paste', this.onInputFieldChange.bind(this));
        });
    }

    onInputFieldChange(event) {
        const field = $(event.target);
        const fieldName = field.attr('name');

        // Validate only on changes
        if (field.data('oldVal') === field.val()) {
            return;
        }
        field.data('oldVal', field.val());

        // Validate the field
        if (field.val().trim().length === 0) {
            this.markFieldAsInvalid({
                field,
                fieldId: fieldName,
                errorCode: `invalid_${fieldName}`,
            });
        } else {
            this.markFieldAsValid({
                field,
                fieldId: fieldName,
            });
        }
    }

    /**
     * Persists the stripe payment method in the stripe payment method settings for later use when creating the
     * payment intent in the payment handler.
     */
    updateStripeShopwarePaymentMethodSettings(payload) {
        document.cookie = "card=".concat(JSON.stringify(payload));
        return new Promise((resolve, reject) => {
            try {
                this.httpClient.patch(
                    this.options.updateStripeShopwarePaymentMethodSettingsUrl,
                    JSON.stringify(payload),
                    (res) => resolve(res),
                );
            } catch (err) {
                reject(err);
            }
        });
    }

    /**
     * Creates a Stripe UI element for a given elementType and mounts it to the given mountSelector
     * in the stripe card form.
     */
    createAndMountStripeUiElement({ elementType, mountSelector, defaultOptions = {} }) {
        // Create the element and add the change listener
        const element = this.stripeUiElementManager.create(elementType, defaultOptions);

        element.on('change', (event) => {
            const field = $(mountSelector);
            if (event.error && event.error.type === 'validation_error') {
                this.markFieldAsInvalid({
                    field,
                    fieldId: elementType,
                    errorCode: event.error.code,
                    fallbackErrorMessage: event.error.message,
                });
            } else {
                this.markFieldAsValid({
                    field,
                    fieldId: elementType,
                });
            }
        });

        // Mount it to the DOM
        const domElementToMountTo = this.getFormElement(mountSelector).get(0);
        element.mount(domElementToMountTo);

        return element;
    }

    /**
     * Checks the list of invalid fields and stripe errors for any entries and, if found, joins them to a list of
     * errors, which is then displayed in the error box. If no invalid fields or stripe errors are found, the error box
     * is hidden.
     */
    updateErrorDisplay() {
        const errorDisplay = this.getErrorDisplay();
        const errorDisplayContent = errorDisplay.find('.alert-content');
        errorDisplayContent.empty();
        const errorMessages = this.getErrorMessages();
        if (errorMessages.length > 0) {
            // Update the error box message and make it visible
            const listElement = $('<ul></ul>').addClass('alert-list').appendTo(errorDisplayContent);
            errorMessages.forEach((errorMessage) => {
                $('<li></li>').text(errorMessage).appendTo(listElement);
            });
            errorDisplay.show();
            errorDisplay.css('align-items', 'normal')
            errorDisplay.css('display', 'flex')
        } else {
            errorDisplay.css('align-items', 'normal')
            errorDisplay.hide();
        }
    }

    getErrorMessages() {
        const invalidFieldErrorMessages = Object.keys(this.invalidFields).map((key) => this.invalidFields[key]);
        const stripeErrorMessages = Object.keys(this.stripeApiErrors).map((key) => this.stripeApiErrors[key]);
        const deduplicatedErrorMessages = stripeErrorMessages.filter((errorMessage) => {
            const errorExistsAsInvalidField = invalidFieldErrorMessages.some(
                (invalidFieldsErrorMessage) => invalidFieldsErrorMessage === errorMessage,
            );

            return !errorExistsAsInvalidField;
        });
        const genericErrorMessages = Object.keys(this.genericErrors).map((key) => this.genericErrors[key]);

        return deduplicatedErrorMessages.concat(invalidFieldErrorMessages).concat(genericErrorMessages);
    }

    /**
     * Removes the validation error for the field with the given 'fieldId' and triggers an update of the displayed
     * validation errors.
     */
    markFieldAsValid({
        field,
        fieldId,
    }) {
        field.removeClass('is-invalid');
        delete this.invalidFields[fieldId];
        this.updateErrorDisplay();
    }

    /**
     * Determines the error message based on the given 'errorCode' and 'fallbackErrorMessage' and triggers
     * an update of the displayed validation errors.
     */
    markFieldAsInvalid({
        field,
        fieldId,
        errorCode,
        fallbackErrorMessage,
    }) {
        field.addClass('is-invalid');
        this.invalidFields[fieldId] = this.translateErrorMessage(errorCode, fallbackErrorMessage);
        this.updateErrorDisplay();
    }

    handleStripeApiError(stripeError) {
        this.stripeApiErrors[stripeError.code] = this.translateErrorMessage(stripeError.code, stripeError.message);
        this.updateErrorDisplay();
        this.resetOrderFormSubmitButtonAndScrollToElement();
    }

    handleGenericError(errorCode) {
        this.genericErrors[errorCode] = this.translateErrorMessage(errorCode);
        this.updateErrorDisplay();
        this.resetOrderFormSubmitButtonAndScrollToElement();
    }

    translateErrorMessage(errorCode, fallbackMessage = null) {
        return this.options.snippets.errors[errorCode] || fallbackMessage || 'Unknown error';
    }

    resetOrderFormSubmitButtonAndScrollToElement() {
        resetOrderFormSubmitButton();
        this.el.scrollIntoView({ behavior: 'smooth' });
    }

    /**
     * @returns {jQuery}
     */
    getErrorDisplay() {
        return this.getFormElement(`#${this.getFormId()}-errors`);
    }

    getFormId() {
        const form = this.getForm();

        return form.attr('id');
    }

    /**
     * @returns {jQuery}
     */
    getForm() {
        return getPaymentMethodForm(this.el);
    }

    /**
     * Applies a jQuery query on the DOM tree under the stripe card selection form using the given selector. This method
     * should be used when selecting any fields that are part of a Stripe card selection payment form.
     *
     * @return {jQuery}
     */
    getFormElement(selector) {
        return this.getForm().find(selector);
    }
}
