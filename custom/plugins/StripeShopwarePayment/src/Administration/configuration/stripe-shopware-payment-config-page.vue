
<template>
  <sw-page>
    <template #smart-bar-header>
      <h2>
        {{ $t('sw-settings.index.title') }}
        <sw-icon
            small
            name="regular-chevron-right-xs"
        />
        {{ $t('stripe-shopware-payment-config-page.smart-bar.title') }}
      </h2>
    </template>

    <template #smart-bar-actions>
      <sw-button :routerLink="{ name: 'sw.settings.index' }">
        {{ $t('stripe-shopware-payment-config-page.smart-bar.cancel') }}
      </sw-button>

      {% block stripe_shopware_payment__payment_config_page__save_settings_button %}
      <sw-button-process
          variant="primary"
          :isLoading="isLoading"
          :disabled="isLoading"
          :processSuccess="isSaveSuccessful"
          @click="saveConfig"
      >
        {{ $t('stripe-shopware-payment-config-page.smart-bar.save') }}
      </sw-button-process>
      {% endblock %}
    </template>

    <template #content>
      {% block stripe_shopware_payment__payment_config_page__main_content %}
      <sw-card-view>
        {% block stripe_shopware_payment__payment_config_page__above_system_config_card %}
        {% endblock %}

        <sw-system-config
            ref="systemConfig"
            salesChannelSwitchable
            :domain="configDomain"
            @config-changed="updateConfig"
        />

        {% block stripe_shopware_payment__payment_config_page__below_system_config_card %}
        {% endblock %}
      </sw-card-view>
      {% endblock %}
    </template>
  </sw-page>
</template>

<script>
const { Mixin } = Shopware;

const WEBHOOK_RESULT_CREATED = 'created';
const WEBHOOK_RESULT_UPDATED = 'updated';
const WEBHOOK_RESULT_NO_CHANGES = 'no_changes';
const STRIPE_CONFIG_DOMAIN = 'StripeShopwarePayment.sales-channel-plugin-config';

export default {
  name: 'stripe-shopware-payment-config-page',

  mixins: [
    Mixin.getByName('notification'),
  ],

  inject: [
    'stripeShopwarePaymentWebhookRegistrationService',
    'stripeShopwarePaymentConfigApiService',
  ],

  data() {
    return {
      isLoading: false,
      isSaveSuccessful: false,
      config: null,
      configDomain: STRIPE_CONFIG_DOMAIN,
      publishableKeyValid: false,
      secretKeyValid: false,
    };
  },

  computed: {
    globalConfig() {
      return this.$refs.systemConfig.actualConfigData.null || {};
    },
  },

  metaInfo() {
    return { title: this.$createTitle(this.$t('stripe-shopware-payment-config-page.title')) };
  },

  methods: {
    async saveConfig() {
      this.isLoading = true;

      try {
        await this.$refs.systemConfig.saveAll();
        this.isSaveSuccessful = true;
        this.createNotificationSuccess({
          title: this.$t('stripe-shopware-payment-config-page.controller.titles.success'),
          message: this.$t('stripe-shopware-payment-config-page.controller.messages.save.success'),
        });
      } catch (error) {
        this.isSaveSuccessful = false;
        let errorMessage = error.response && error.response.data;
        if (!errorMessage) {
          errorMessage = this.$t('stripe-shopware-payment-config-page.controller.messages.unknown-error');
        }
        this.createNotificationError({
          title: this.$t('stripe-shopware-payment-config-page.controller.titles.error'),
          message: this.$t(
              'stripe-shopware-payment-config-page.controller.messages.save.error',
              { errorMessage },
          ),
        });
        this.isLoading = false;

        return;
      }

      await this.validateCredentials();
      // Only register a webhook for the current sales channel when it has its own secret key to prevent the
      // creation of multiple webhook endpoints for the same (global) credentials.
      if (this.secretKeyValid && this.getConfigValue(this.config, 'stripeSecretKey')) {
        try {
          await Promise.all([
            this.registerWebhook(),
            this.updateStripeAccountCountry(),
          ]);
        } finally {
          // It is currently not possible to reload the config, delete the current config and fetch it
          // again instead.
          delete this.$refs.systemConfig.actualConfigData[this.$refs.systemConfig.currentSalesChannelId];
          await this.$refs.systemConfig.readAll();
        }
      }

      this.isLoading = false;
    },

    updateConfig(config) {
      this.config = config;
      this.isSaveSuccessful = false;
    },

    async validateCredentials() {
      const publishableKey = this.getConfigValueWithInheritance('stripePublicKey');
      const secretKey = this.getConfigValueWithInheritance('stripeSecretKey');
      const validationPromises = [];
      validationPromises.push(this.stripeShopwarePaymentConfigApiService.validateSecretKey(
          secretKey,
      ));
      if (publishableKey) {
        validationPromises.push(this.stripeShopwarePaymentConfigApiService.validatePublishableKey(
            publishableKey,
        ));
      } else {
        validationPromises.push(true);
      }
      try {
        [this.secretKeyValid, this.publishableKeyValid] = await Promise.all(validationPromises);
      } catch (error) {
        this.createNotificationError({
          title: this.$t('stripe-shopware-payment-config-page.controller.titles.error'),
          message: this.$t('stripe-shopware-payment-config-page.controller.messages.credentials.error', {
            errorMessage: error.message,
          }),
        });

        return;
      }
      this.showCredentialsNotification();
    },

    async registerWebhook() {
      try {
        const currentSalesChannelId = this.$refs.systemConfig.currentSalesChannelId;
        const response = await this.stripeShopwarePaymentWebhookRegistrationService.registerWebhook(
            currentSalesChannelId,
        );
        switch (response.result) {
          case WEBHOOK_RESULT_CREATED:
            this.createNotificationSuccess({
              title: this.$t('stripe-shopware-payment-config-page.controller.titles.success'),
              message: this.$t('stripe-shopware-payment-config-page.controller.messages.webhook.created'),
            });
            break;
          case WEBHOOK_RESULT_UPDATED:
            this.createNotificationSuccess({
              title: this.$t('stripe-shopware-payment-config-page.controller.titles.success'),
              message: this.$t('stripe-shopware-payment-config-page.controller.messages.webhook.updated'),
            });
            break;
          case WEBHOOK_RESULT_NO_CHANGES:
            break;
          default:
            this.createNotificationError({
              title: this.$t('stripe-shopware-payment-config-page.controller.titles.error'),
              message: this.$t(
                  'stripe-shopware-payment-config-page.controller.messages.webhook.error',
                  {
                    errorMessage: this.$t(
                        'stripe-shopware-payment-config-page.controller.messages.unknown-error',
                    ),
                  },
              ),
            });

            return;
        }
      } catch (error) {
        let errorMessage = error.response.data.result;
        if (!errorMessage) {
          errorMessage = this.$t('stripe-shopware-payment-config-page.controller.messages.unknown-error');
        }
        this.createNotificationError({
          title: this.$t('stripe-shopware-payment-config-page.controller.titles.error'),
          message: this.$t(
              'stripe-shopware-payment-config-page.controller.messages.webhook.error',
              { errorMessage },
          ),
        });
      }
    },

    showCredentialsNotification() {
      if (this.secretKeyValid && this.publishableKeyValid) {
        this.createNotificationSuccess({
          title: this.$t('stripe-shopware-payment-config-page.controller.titles.success'),
          message: this.$t('stripe-shopware-payment-config-page.controller.messages.credentials.valid'),
        });
      } else {
        const invalidFields = [];
        if (!this.secretKeyValid) {
          invalidFields.push(this.$t(
              'stripe-shopware-payment-config-page.controller.messages.credentials.stripeSecretKey',
          ));
        }
        if (!this.publishableKeyValid) {
          invalidFields.push(this.$t(
              'stripe-shopware-payment-config-page.controller.messages.credentials.stripePublishableKey',
          ));
        }
        this.createNotificationError({
          title: this.$t('stripe-shopware-payment-config-page.controller.titles.error'),
          message: this.$t('stripe-shopware-payment-config-page.controller.messages.credentials.invalid', {
            invalidFields: invalidFields.join(', '),
          }),
        });
      }
    },

    getConfigValue(config, configKey) {
      return config[`${STRIPE_CONFIG_DOMAIN}.${configKey}`];
    },

    getConfigValueWithInheritance(configKey) {
      return this.getConfigValue(this.config, configKey) || this.getConfigValue(this.globalConfig, configKey);
    },

    async updateStripeAccountCountry() {
      const currentSalesChannelId = this.$refs.systemConfig.currentSalesChannelId;
      try {
        await this.stripeShopwarePaymentConfigApiService.updateStripeAccountCountry({
          salesChannelId: currentSalesChannelId,
        });
      } catch (error) {
        const errorMessage = error.response.data.errors[0].detail;
        this.createNotificationError({
          title: this.$t('stripe-shopware-payment-config-page.controller.titles.error'),
          message: this.$t(
              'stripe-shopware-payment-config-page.controller.messages.update-account-country.error',
              { errorMessage },
          ),
        });
      }
    },
  },
};
</script>

<i18n>
{
  "en-GB": {
    "stripe-shopware-payment-config-page": {
      "title": "Stripe",
      "smart-bar": {
        "title": "Stripe",
        "save": "Save",
        "cancel": "Cancel"
      },
      "controller": {
        "titles": {
          "success": "Success",
          "error": "Error"
        },
        "messages": {
          "save": {
            "success": "The configuration was saved successfully.",
            "error": "The configuration could not be saved, because the following error occurred: {errorMessage}"
          },
          "webhook": {
            "created": "The Stripe webhook was created successfully.",
            "updated": "The Stripe webhook was updated successfully.",
            "error": "The Stripe webhook could not be saved, because the following error occurred: {errorMessage}"
          },
          "credentials": {
            "stripeSecretKey": "Secret Stripe API Key",
            "stripePublishableKey": "Publishable Stripe API Key",
            "valid": "The Stripe API Credentials are valid.",
            "invalid": "The following Stripe API Credentials are invalid: {invalidFields}",
            "error": "The validation of the Stripe API Credentials failed, because the following error occurred: {errorMessage}"
          },
          "update-account-country": {
            "error": "The country of the Stripe account could not be retrieved automatically: {errorMessage}"
          },
          "unknown-error": "Unknown error"
        }
      }
    }
  },
  "de-DE": {
    "stripe-shopware-payment-config-page": {
      "title": "Stripe",
      "smart-bar": {
        "title": "Stripe",
        "save": "Speichern",
        "cancel": "Abbrechen"
      },
      "controller": {
        "titles": {
          "success": "Erfolg",
          "error": "Fehler"
        },
        "messages": {
          "save": {
            "success": "Die Konfiguration wurde erfolgreich gespeichert.",
            "error": "Die Konfiguration konnte nicht gespeichert werden, da folgender Fehler aufgetreten ist: {errorMessage}"
          },
          "webhook": {
            "created": "Der Stripe Webhook wurde erfolgreich erstellt.",
            "updated": "Der Stripe Webhook wurde erfolgreich aktualisiert.",
            "error": "Der Stripe Webhook konnte nicht gespeichert werden, da folgender Fehler aufgetreten ist: {errorMessage}"
          },
          "credentials": {
            "stripeSecretKey": "Geheimer Stripe-API-Schlüssel",
            "stripePublishableKey": "Veröffentlichbarer Stripe-API-Schlüssel",
            "valid": "Die Stripe API Zugangsdaten sind gültig.",
            "invalid": "Die folgenden Stripe API Zugangsdaten sind ungültig: {invalidFields}",
            "error": "Die Validierung der Stripe API Zugangsdaten ist fehlgeschlagen, da folgender Fehler aufgetreten ist: {errorMessage}"
          },
          "update-account-country": {
            "error": "Das Land des Stripe-Accounts konnte nicht automatisch abgerufen werden: {errorMessage}"
          },
          "unknown-error": "Unbekannter Fehler"
        }
      }
    }
  }
}
</i18n>
