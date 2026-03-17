<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Stripe\ShopwarePayment\Installation;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;
use Shopware\Core\System\Language\LanguageEntity;
use Stripe\ShopwarePayment\StripeShopwarePayment;

class StripePaymentInstaller
{
    private const PAYMENT_METHODS = [
        'stripe.shopware_payment.payment_handler.card' => [
            'technicalName' => 'stripe_shopware_payment_payment_handler_card',
            'translations' => [
                'en-GB' => [
                    'name' => 'Credit Card (via Stripe)',
                ],
                'de-DE' => [
                    'name' => 'Kreditkarte (via Stripe)',
                ],
            ]
        ],
        'stripe.shopware_payment.payment_handler.digital_wallets' => [
            'technicalName' => 'stripe_shopware_payment_payment_handler_digital_wallets',
            'translations' => [
                'en-GB' => [
                    'name' => 'Apple Pay / Google Pay (via Stripe)',
                ],
                'de-DE' => [
                    'name' => 'Apple Pay / Google Pay (via Stripe)',
                ],
            ]
        ],
        'stripe.shopware_payment.payment_handler.sepa' => [
            'technicalName' => 'stripe_shopware_payment_payment_handler_sepa',
            'translations' => [
                'en-GB' => [
                    'name' => 'SEPA Direct Debit (via Stripe)',
                ],
                'de-DE' => [
                    'name' => 'SEPA-Lastschrift (via Stripe)',
                ],
            ]
        ],
        'stripe.shopware_payment.payment_handler.klarna' => [
            'technicalName' => 'stripe_shopware_payment_payment_handler_klarna',
            'translations' => [
                'en-GB' => [
                    'name' => 'Klarna (via Stripe)',
                ],
                'de-DE' => [
                    'name' => 'Klarna (via Stripe)',
                ],
            ]
        ],
        'stripe.shopware_payment.payment_handler.ideal' => [
            'technicalName' => 'stripe_shopware_payment_payment_handler_ideal',
            'translations' => [
                'en-GB' => [
                    'name' => 'iDeal (via Stripe)',
                ],
                'de-DE' => [
                    'name' => 'iDeal (via Stripe)',
                ],
            ]
        ],
        'stripe.shopware_payment.payment_handler.p24' => [
            'technicalName' => 'stripe_shopware_payment_payment_handler_p24',
            'translations' => [
                'en-GB' => [
                    'name' => 'P24 (via Stripe)',
                ],
                'de-DE' => [
                    'name' => 'P24 (via Stripe)',
                ],
            ]
        ],
        'stripe.shopware_payment.payment_handler.eps' => [
            'technicalName' => 'stripe_shopware_payment_payment_handler_eps',
            'translations' => [
                'en-GB' => [
                    'name' => 'EPS (via Stripe)',
                ],
                'de-DE' => [
                    'name' => 'EPS (via Stripe)',
                ],
            ]
        ],
        'stripe.shopware_payment.payment_handler.bancontact' => [
            'technicalName' => 'stripe_shopware_payment_payment_handler_bancontact',
            'translations' => [
                'en-GB' => [
                    'name' => 'Bancontact (via Stripe)',
                ],
                'de-DE' => [
                    'name' => 'Bancontact (via Stripe)',
                ],
            ]
        ],
    ];

    private const DEPRECATED_PAYMENT_METHODS = [
        'stripe.shopware_payment.payment_handler.giropay',
    ];

    /**
     * @var Context
     */
    private $context;

    /**
     * @var PluginIdProvider
     */
    private $pluginIdProvider;

    /**
     * @var EntityRepository
     */
    private $paymentMethodRepository;

    /**
     * @var EntityRepository
     */
    private $languageRepository;

    public function __construct(
        Context $context,
        PluginIdProvider $pluginIdProvider,
        EntityRepository $paymentMethodRepository,
        EntityRepository $languageRepository
    ) {
        $this->context = $context;
        $this->pluginIdProvider = $pluginIdProvider;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->languageRepository = $languageRepository;
    }

    public function postInstall(): void
    {
        $this->postUpdate();
    }

    public function postUpdate(): void
    {
        $this->ensurePaymentMethods();
    }

    public function activate(): void
    {
        $this->activatePaymentMethods();
    }

    public function deactivate(): void
    {
        $this->deactivatePaymentMethods();
    }

    private function activatePaymentMethods(): void
    {
        foreach (self::PAYMENT_METHODS as $paymentMethodHandlerIdentifier => $data) {
            $paymentMethodId = $this->getPaymentMethodIdForHandlerIdentifier($paymentMethodHandlerIdentifier);
            if (!$paymentMethodId) {
                continue;
            }

            $this->paymentMethodRepository->update([
                [
                    'id' => $paymentMethodId,
                    'active' => true,
                ],
            ], $this->context);
        }
    }

    private function deactivatePaymentMethods(): void
    {
        foreach (self::PAYMENT_METHODS as $paymentMethodHandlerIdentifier => $data) {
            $paymentMethodId = $this->getPaymentMethodIdForHandlerIdentifier($paymentMethodHandlerIdentifier);
            if (!$paymentMethodId) {
                continue;
            }

            $this->paymentMethodRepository->update([
                [
                    'id' => $paymentMethodId,
                    'active' => false,
                ],
            ], $this->context);
        }
    }

    private function ensurePaymentMethods(): void
    {
        $defaultLocaleCode = $this->getSystemDefaultLocaleCode($this->context);
        foreach (self::PAYMENT_METHODS as $paymentMethodHandlerIdentifier => $data) {
            $translations = $data['translations'];
            $paymentMethodId = $this->getPaymentMethodIdForHandlerIdentifier($paymentMethodHandlerIdentifier);
            if ($defaultLocaleCode && !isset($translations[$defaultLocaleCode])) {
                $translations[$defaultLocaleCode] = $translations['en-GB'];
            }

            $this->paymentMethodRepository->upsert([
                [
                    'id' => $paymentMethodId,
                    'handlerIdentifier' => $paymentMethodHandlerIdentifier,
                    'pluginId' => $this->getPluginId(),
                    'translations' => $translations,
                    'technicalName' => $data['technicalName'],
                ],
            ], $this->context);
        }

        foreach (self::DEPRECATED_PAYMENT_METHODS as $deprecatedHandlerIdentifier) {
            $paymentMethodId = $this->getPaymentMethodIdForHandlerIdentifier($deprecatedHandlerIdentifier);
            if ($paymentMethodId !== null) {
                $this->paymentMethodRepository->update([
                    [
                        'id' => $paymentMethodId,
                        'active' => false,
                    ],
                ], $this->context);
            }
        }
    }

    private function getPaymentMethodIdForHandlerIdentifier(string $paymentMethodHandlerIdentifier): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('handlerIdentifier', $paymentMethodHandlerIdentifier));

        return $this->paymentMethodRepository
            ->searchIds($criteria, $this->context)
            ->firstId();
    }

    private function getPluginId(): string
    {
        return $this->pluginIdProvider->getPluginIdByBaseClass(
            StripeShopwarePayment::class,
            $this->context,
        );
    }

    private function getSystemDefaultLocaleCode(Context $context): ?string
    {
        $criteria = new Criteria([Defaults::LANGUAGE_SYSTEM]);
        $criteria->addAssociation('locale');
        /** @var LanguageEntity $systemDefaultLanguage */
        $systemDefaultLanguage = $this->languageRepository->search($criteria, $context)->first();
        $locale = $systemDefaultLanguage->getLocale();
        if (!$locale) {
            return null;
        }

        return $locale->getCode();
    }
}
