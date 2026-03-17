import {setOrderFormSubmitButtonLoading, getOrderFormSubmitButton, getOrderForm} from './checkout-page-util.js';
import PaymentMethodSelectionPlugin from './payment-method-selection-plugin.js';

import $ from "jquery";

export default class SepaBankAccountSelectionPlugin extends PaymentMethodSelectionPlugin {
    get defaultOptions() {
        return {
            stripeApiVersion: undefined,
            stripePublicKey: '',
            selectedSepaBankAccount: null,
            availableSepaBankAccounts: [],
            isSavingSepaBankAccountsAllowed: false,
            salesChannelLocale: 'de',
            snippets: {},
        };
    }

    init() {
        super.init();

        if(!window.Stripe) {
            this.handleGenericError("error-stripe-js-loading")
            getOrderFormSubmitButton().prop('disabled', 'disabled');
            return;
        }

        this.inputFields = [
            this.getFormElement('#stripe-shopware-payment-sepa-bank-account-selection-account-owner-input'),
            this.getFormElement('#stripe-shopware-payment-sepa-bank-account-selection-email-input'),
            this.getFormElement('#stripe-shopware-payment-sepa-bank-account-selection-street-input'),
            this.getFormElement('#stripe-shopware-payment-sepa-bank-account-selection-zip-code-input'),
            this.getFormElement('#stripe-shopware-payment-sepa-bank-account-selection-city-input'),
        ];

        this.selectedSepaBankAccount = this.options.selectedSepaBankAccount;
        this.selectedSepaBankAccountChanged = null;

        this.mountForm();
        this.showForm();
    }

    mountForm() {
        super.mountForm();

        // Ensure the previously selected SEPA bank account is also selected in the available SEPA bank accounts
        // dropdown
        if (this.selectedSepaBankAccount) {
            const savedSepaBankAccountsSelect = this.getFormElement(
                '#stripe-shopware-payment-sepa-bank-account-selection-saved-sepa-bank-accounts-select',
            );
            savedSepaBankAccountsSelect.val(this.selectedSepaBankAccount.id);
            savedSepaBankAccountsSelect.trigger('change');
        }
    }

    mountStripeUiElements() {
        // Define options to apply to all fields when creating them
        const accountOwnerFieldEl = this.getFormElement(
            '#stripe-shopware-payment-sepa-bank-account-selection-account-owner-input',
        );
        const defaultOptions = {
            style: {
                base: {
                    color: accountOwnerFieldEl.css('color'),
                    fontFamily: accountOwnerFieldEl.css('font-family'),
                    fontSize: accountOwnerFieldEl.css('font-size'),
                    fontWeight: accountOwnerFieldEl.css('font-weight'),
                    lineHeight: accountOwnerFieldEl.css('line-height'),
                },
            },
        };

        this.stripeUiElements = [
            this.createAndMountStripeUiElement({
                elementType: 'iban',
                mountSelector: '#stripe-shopware-payment-sepa-bank-account-selection-iban-input',
                defaultOptions: {
                    ...defaultOptions,
                    supportedCountries: ['SEPA'],
                    placeholderCountry: this.options.countryIsoCode,
                },
            }),
        ];
    }

    addFormListeners() {
        super.addFormListeners();

        this.getFormElement('#stripe-shopware-payment-sepa-bank-account-selection-saved-sepa-bank-accounts-select').on(
            'change',
            this.onSepaBankAccountSelectionChange.bind(this),
        );

        // Prevent the shopware auto-submit plugin from auto submitting the form for these field changes
        this.getFormElement('#stripe-shopware-payment-sepa-bank-account-selection-saved-sepa-bank-accounts-select').on(
            'change',
            (event) => event.stopPropagation(),
        );
        this.getFormElement('#stripe-shopware-payment-sepa-bank-account-selection-country-select').on(
            'change',
            (event) => event.stopPropagation(),
        );
        this.inputFields.forEach((inputField) => inputField.on('change', (event) => event.stopPropagation()));
        this.getFormElement('#stripe-shopware-payment-sepa-bank-account-selection-save-sepa-bank-account-checkbox').on(
            'change',
            (event) => event.stopPropagation(),
        );
    }

    /**
     * Hides all form fields if an existing SEPA bank account is selected. If the 'new' option is selected, all fields
     * are made visible.
     */
    onSepaBankAccountSelectionChange(event) {
        const elem = $(event.target);

        if (elem.val() === 'new') {
            // A new, empty SEPA bank account was selected
            this.selectedSepaBankAccount = null;

            // Make validation errors visible
            this.updateErrorDisplay();

            // Show the save check box
            this.getFormElement(
                '.stripe-shopware-payment-sepa-bank-account-selection__new-sepa-bank-account-form',
            ).show();
            this.inputFields.forEach((inputField) => inputField.prop('disabled', false));
            if (this.options.isSavingSepaBankAccountsAllowed) {
                this.getFormElement(
                    '#stripe-shopware-payment-sepa-bank-account-selection-save-sepa-bank-account-checkbox',
                ).show().prop('checked', true);
            }

            return;
        }

        // Find the selected SEPA bank account
        const sepaBankAccount = this.options.availableSepaBankAccounts.find(
            (sepaBankAccount) => sepaBankAccount.id === elem.val(),
        );
        if (!sepaBankAccount) {
            return;
        }
        if (!this.selectedSepaBankAccount || this.selectedSepaBankAccount.id !== sepaBankAccount.id) {
            this.selectedSepaBankAccount = sepaBankAccount;
            this.selectedSepaBankAccountChanged = true;
        }

        this.getFormElement('.stripe-shopware-payment-sepa-bank-account-selection__new-sepa-bank-account-form').hide();
        this.inputFields.forEach((inputField) => inputField.prop('disabled', true));
    }

    createStripeShopwarePaymentMethodForSepaBankAccount() {
        return this.stripeApiClient.createPaymentMethod({
            type: 'sepa_debit',
            sepa_debit: this.stripeUiElementManager.getElement('iban'),
            billing_details: {
                name: this.getFormElement(
                    '#stripe-shopware-payment-sepa-bank-account-selection-account-owner-input',
                ).val(),
                email: this.getFormElement('#stripe-shopware-payment-sepa-bank-account-selection-email-input').val(),
                address: {
                    line1: this.getFormElement(
                        '#stripe-shopware-payment-sepa-bank-account-selection-street-input',
                    ).val(),
                    postal_code: this.getFormElement(
                        '#stripe-shopware-payment-sepa-bank-account-selection-zip-code-input',
                    ).val(),
                    city: this.getFormElement('#stripe-shopware-payment-sepa-bank-account-selection-city-input').val(),
                    country: this.getFormElement(
                        '#stripe-shopware-payment-sepa-bank-account-selection-country-select',
                    ).val(),
                },
            },
        });
    }

    /**
     * Ensures a stripe SEPA bank account was selected or a new one is created from the information entered into the
     * stripe sepa bank account form. Additionally ensures the stripe SEPA bank account is persisted in the stripe
     * payment method settings so that we can create the payment intent in the payment handler.
     */
    async onFormSubmission(event) {
        let form = null;
        if(event) {
            form = $(event.target).get(0)
        } else {
            form = getOrderForm().get(0);
        }

        if (this.selectedSepaBankAccount && !this.selectedSepaBankAccountChanged) {
            form.submit(event, true);
            return;
        }

        if(event) {
            event.preventDefault();
        }

        this.stripeApiErrors = {};
        let saveSepaBankAccountForFutureCheckouts;
        if (!this.selectedSepaBankAccount) {
            if (Object.keys(this.invalidFields).length > 0) {
                this.resetOrderFormSubmitButtonAndScrollToElement();

                return;
            }
            setOrderFormSubmitButtonLoading();

            let result;
            try {
                result = await this.createStripeShopwarePaymentMethodForSepaBankAccount();
            } catch (error) {
                this.handleGenericError('error-during-saving');

                throw error;
            }
            if (result.error) {
                this.handleStripeApiError(result.error);

                return;
            }

            // Save the SEPA bank account information
            const sepaBankAccount = result.paymentMethod.sepa_debit;
            sepaBankAccount.id = result.paymentMethod.id;
            sepaBankAccount.name = this.getFormElement(
                '#stripe-shopware-payment-sepa-bank-account-selection-account-owner-input',
            ).val();
            this.selectedSepaBankAccount = sepaBankAccount;
            this.selectedSepaBankAccountChanged = true;

            // Persist the SEPA bank account in the payment method settings
            const isSaveSepaBankAccountBoxChecked = this.getFormElement(
                '#stripe-shopware-payment-sepa-bank-account-selection-save-sepa-bank-account-checkbox',
            ).is(':checked');
            saveSepaBankAccountForFutureCheckouts = this.options.isSavingSepaBankAccountsAllowed
                && isSaveSepaBankAccountBoxChecked;
        }

        try {
            await this.updateStripeShopwarePaymentMethodSettings({
                sepaBankAccount: this.selectedSepaBankAccount,
                saveSepaBankAccountForFutureCheckouts,
            });
        } catch (error) {
            this.handleGenericError('error-during-saving');

            throw error;
        }
        this.selectedSepaBankAccountChanged = null;

        // Submit the form again to finish the payment selection process
        form.submit(event, true);
    }
}
