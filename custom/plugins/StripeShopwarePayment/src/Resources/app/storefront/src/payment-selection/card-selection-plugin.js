import {getOrderFormSubmitButton, setOrderFormSubmitButtonLoading, getOrderForm} from './checkout-page-util.js';
import PaymentMethodSelectionPlugin from './payment-method-selection-plugin.js';

import $ from "jquery";

export default class CardSelectionPlugin extends PaymentMethodSelectionPlugin {
    get defaultOptions() {
        return {
            stripeApiVersion: undefined,
            stripePublicKey: '',
            selectedCard: null,
            availableCards: [],
            isSavingCreditCardsAllowed: false,
            salesChannelLocale: 'de',
            snippets: {},
        };
    }

    init() {
        super.init();

        if(!window.Stripe) {
            this.handleGenericError("error-stripe-js-loading")
            getOrderFormSubmitButton().attr('disabled', 'disabled');
            return;
        }

        this.inputFields = [
            this.getFormElement('#stripe-shopware-payment-card-selection-card-holder-input'),
        ];

        this.selectedCard = this.options.selectedCard;
        this.selectedCardChanged = null;

        this.mountForm();
        this.showForm();
    }

    mountForm() {
        super.mountForm();

        // Ensure the previously selected card is also selected in the available cards dropdown
        if (this.selectedCard) {
            this.getFormElement('#stripe-shopware-payment-card-selection-saved-cards-select').val(this.selectedCard.id);
            this.getFormElement('#stripe-shopware-payment-card-selection-saved-cards-select').trigger('change');
        }
    }

    mountStripeUiElements() {
        // Define options to apply to all fields when creating them
        const cardHolderFieldEl = this.getFormElement('#stripe-shopware-payment-card-selection-card-holder-input');
        const defaultOptions = {
            style: {
                base: {
                    color: cardHolderFieldEl.css('color'),
                    fontFamily: cardHolderFieldEl.css('font-family'),
                    fontSize: cardHolderFieldEl.css('font-size'),
                    fontWeight: cardHolderFieldEl.css('font-weight'),
                    lineHeight: "21px",
                },
            },
        };

        // Create all elements
        this.stripeUiElements = [
            this.createAndMountStripeUiElement({
                elementType: 'cardNumber',
                mountSelector: '#stripe-shopware-payment-card-selection-card-number-input',
                defaultOptions: {
                    ...defaultOptions,
                    showIcon: true,
                },
            }),
            this.createAndMountStripeUiElement({
                elementType: 'cardExpiry',
                mountSelector: '#stripe-shopware-payment-card-selection-expiry-input',
                defaultOptions,
            }),
            this.createAndMountStripeUiElement({
                elementType: 'cardCvc',
                mountSelector: '#stripe-shopware-payment-card-selection-card-cvc-input',
                defaultOptions,
            }),
        ];
    }

    addFormListeners() {
        super.addFormListeners();

        this.getFormElement('#stripe-shopware-payment-card-selection-saved-cards-select')
            ?.on('change', this.onCardSelectionChange.bind(this));

        // Prevent the shopware auto-submit plugin from auto submitting the form for these field changes
        this.getFormElement('#stripe-shopware-payment-card-selection-saved-cards-select')?.on(
            'change',
            (event) => event.stopPropagation(),
        );
        this.inputFields.forEach((inputField) => inputField.on('change', (event) => event.stopPropagation()));
        this.getFormElement('#stripe-shopware-payment-card-selection-save-card-checkbox')?.on(
            'change',
            (event) => event.stopPropagation(),
        );
    }

    /**
     * Hides all form fields if an existing card is selected. If the 'new' option is selected, all fields are made
     * visible.
     */
    onCardSelectionChange(event) {
        const elem = $(event.target);

        if (elem.val() === 'new') {
            // A new, empty card was selected
            this.selectedCard = null;

            // Make validation errors visible
            this.updateErrorDisplay();

            // Show the save check box
            this.getFormElement('.stripe-shopware-payment-card-selection__new-card-form').show();
            this.inputFields.forEach((inputField) => inputField.prop('disabled', false));
            if (this.options.isSavingCreditCardsAllowed) {
                this.getFormElement('#stripe-shopware-payment-card-selection-save-card-checkbox')
                    .show().prop('checked', true);
            }

            return;
        }

        // Find the selected card
        const card = this.options.availableCards.find((card) => card.id === elem.val());
        if (!card) {
            return;
        }
        if (!this.selectedCard || this.selectedCard.id !== card.id) {
            this.selectedCard = card;
            this.selectedCardChanged = true;
        }

        this.getFormElement('.stripe-shopware-payment-card-selection__new-card-form').hide();
        this.inputFields.forEach((inputField) => inputField.prop('disabled', true));
    }

    createStripeShopwarePaymentMethodForCard({ cardNumberStripeUiElement, cardHolder }) {
        return this.stripeApiClient.createPaymentMethod({
            type: 'card',
            card: cardNumberStripeUiElement,
            billing_details: {
                name: cardHolder,
            },
        });
    }

    /**
     * Ensures a stripe card was selected or a new one is created from the information entered into the
     * stripe card form. Additionally ensures the stripe card is persisted in the stripe session so that we can
     * create the payment intent in the payment handler.
     */
    async onFormSubmission(event) {
        let form = null;
        if(event) {
            form = $(event.target).get(0)
        } else {
            form = getOrderForm().get(0);
        }

        if (this.selectedCard && !this.selectedCardChanged) {
            form.submit(event, true);
            return;
        }

        if(event) {
            event.preventDefault();
        }

        this.stripeApiErrors = {};
        let saveCardForFutureCheckouts;
        if (!this.selectedCard) {
            if (Object.keys(this.invalidFields).length > 0) {
                this.resetOrderFormSubmitButtonAndScrollToElement();

                return;
            }
            setOrderFormSubmitButtonLoading();

            // Create the new card via the Stripe API
            const cardHolder = this.getFormElement('#stripe-shopware-payment-card-selection-card-holder-input').val();
            let result;
            try {
                result = await this.createStripeShopwarePaymentMethodForCard({
                    cardNumberStripeUiElement: this.stripeUiElementManager.getElement('cardNumber'),
                    cardHolder,
                });
            } catch (error) {
                this.handleGenericError('error-during-saving');

                throw error;
            }
            if (result.error) {
                this.handleStripeApiError(result.error);

                return;
            }

            // Save the card information
            const card = result.paymentMethod.card;
            card.id = result.paymentMethod.id;
            card.name = cardHolder;
            this.selectedCard = card;
            this.selectedCardChanged = true;

            const isSaveCardBoxChecked = this.getFormElement(
                '#stripe-shopware-payment-card-selection-save-card-checkbox',
            ).is(':checked');
            saveCardForFutureCheckouts = this.options.isSavingCreditCardsAllowed && isSaveCardBoxChecked;
        }

        try {
            await this.updateStripeShopwarePaymentMethodSettings({
                card: this.selectedCard,
                saveCardForFutureCheckouts,
            });
        } catch (error) {
            this.handleGenericError('error-during-saving');

            throw error;
        }
        this.selectedCardChanged = null;

        // Submit the form again to finish the payment selection process
        form.submit(event, true);
    }
}
