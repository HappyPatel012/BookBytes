<?php declare(strict_types=1);

namespace PriceDropAlerts\Service;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Mail\Service\MailService;
use Shopware\Core\Framework\Context;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PriceDropMailService
{
    private const MAIL_TEMPLATE_TYPE = 'bookbytes_price_drop_alert';

    public function __construct(
        private readonly MailService $mailService,
        private readonly Connection $connection,
        private readonly UrlGeneratorInterface $router
    ) {
    }

    public function send(string $salesChannelId, array $customer, array $product, float $currentPrice, Context $context): void
    {
        $mailTemplateTypeId = $this->connection->fetchOne(
            'SELECT id FROM mail_template_type WHERE technical_name = :technicalName LIMIT 1',
            ['technicalName' => self::MAIL_TEMPLATE_TYPE]
        );

        if (!\is_string($mailTemplateTypeId) || $mailTemplateTypeId === '') {
            return;
        }

        $fullName = trim(((string) ($customer['firstName'] ?? '')) . ' ' . ((string) ($customer['lastName'] ?? '')));
        $productId = (string) ($product['id'] ?? '');

        $templateData = [
            'customer' => $customer,
            'product' => $product,
            'currentPrice' => $currentPrice,
            'productUrl' => $this->router->generate('frontend.detail.page', ['productId' => $productId], UrlGeneratorInterface::ABSOLUTE_PATH),
        ];

        $this->mailService->send([
            'recipients' => [
                (string) ($customer['email'] ?? '') => $fullName !== '' ? $fullName : (string) ($customer['email'] ?? ''),
            ],
            'salesChannelId' => $salesChannelId,
            'mailTemplateTypeId' => $mailTemplateTypeId,
        ], $context, $templateData);
    }
}
