<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Stripe\ShopwarePayment;

use Stripe\ShopwarePayment\Payment\PaymentIntentPaymentHandler\PaymentIntentHandlerService;
use Stripe\ShopwarePayment\Payment\PaymentIntentPaymentHandler\PaymentIntentPaymentHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TagModifierPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container): void
    {
        $version = $container->getParameter('kernel.shopware_version');
        $definitions = $container->getDefinitions();
        foreach ($definitions as $definition) {
            if ($definition->getClass() === PaymentIntentHandlerService::class) {
                $definition->clearTags();
                if (version_compare($version, '6.7.0', '>=')) {
                    $definition->addTag('shopware.payment.method');
                } else {
                    $definition->addTag('shopware.payment.method.async');
                }
            }
        }
    }
}
