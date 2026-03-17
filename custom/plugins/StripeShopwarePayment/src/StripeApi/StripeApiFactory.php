<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Stripe\ShopwarePayment\StripeApi;

use Shopware\Core\Framework\Context;
use Stripe\ShopwarePayment\Config\StripePluginConfigService;
use Psr\Log\LoggerInterface;

class StripeApiFactory
{
    /**
     * @var StripePluginConfigService
     */
    private $stripePluginConfigService;

    /**
     * @var StripeApiAppInfoFactory
     */
    private $stripeApiAppInfoFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StripePluginConfigService $stripePluginConfigService,
        StripeApiAppInfoFactory $stripeApiAppInfoFactory,
        LoggerInterface $logger
    ) {
        $this->stripePluginConfigService = $stripePluginConfigService;
        $this->stripeApiAppInfoFactory = $stripeApiAppInfoFactory;
        $this->logger = $logger;
    }

    public function createStripeApiForSalesChannel(Context $context, ?string $salesChannelId = null): StripeApi
    {
        $pluginConfig = $this->stripePluginConfigService->getStripePluginConfigForSalesChannel($salesChannelId);

        $stripeApiAppInfo = $this->stripeApiAppInfoFactory->createStripeApiAppInfoForSalesChannel(
            $context,
            $salesChannelId,
        );

        return new StripeApi($pluginConfig->getStripeApiCredentials(), $stripeApiAppInfo, $this->logger);
    }

    public function createStripeApiForSecretKey(string $secretKey): StripeApi
    {
        return new StripeApi(
            new StripeApiCredentials($secretKey),
            $this->stripeApiAppInfoFactory->createStripeApiAppInfo(),
            $this->logger
        );
    }
}
