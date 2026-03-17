import $ from "jquery";

/**
 * @returns {jQuery}
 */
function getPaymentMethodForm(formSelector) {
    return $('input[id^="paymentMethod"]')
        .closest('.payment-method')
        .find(formSelector);
}

/**
 * @return jQuery
 */
function getOrderForm() {
    return $('#confirmOrderForm');
}

/**
 * @returns {jQuery}
 */
function getOrderFormSubmitButton() {
    return $('#confirmOrderForm button[type="submit"], button[form="confirmOrderForm"]');
}

/**
 * Finds the order form's submit button and resets it by removing the 'disabled' attribute as well as the loading
 * indicator.
 */
function resetOrderFormSubmitButton() {
    const submitButton = getOrderFormSubmitButton();
    $(submitButton).removeAttr('disabled').find('.loader').remove();
}

/**
 * Finds the order form's submit button and adds the 'disabled' attribute as well as a loading indicator to it.
 */
function setOrderFormSubmitButtonLoading() {
    // Reset the button first to prevent adding multiple loading indicators
    resetOrderFormSubmitButton();
    const submitButton = getOrderFormSubmitButton();
    const newContent = $(submitButton).text()
        + '<div class="loader" role="status" style="position: relative;top: 4px;">'
        + '<span class="visually-hidden">Loading...</span>'
        + '</div>';
    $(submitButton).html(newContent).attr('disabled', 'disabled');
}

export {
    getPaymentMethodForm,
    getOrderForm,
    getOrderFormSubmitButton,
    resetOrderFormSubmitButton,
    setOrderFormSubmitButtonLoading,
};
