<?php declare(strict_types=1);

namespace PriceDropAlerts\Service;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Mail\Service\MailService;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PriceDropMailService
{
    private const MAIL_TEMPLATE_TYPE = 'bookbytes_price_drop_alert';

    public function __construct(
        private readonly MailService $mailService,
        private readonly Connection $connection,
        private readonly UrlGeneratorInterface $router,
        private readonly LoggerInterface $logger,
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    public function send(string $salesChannelId, array $customer, array $product, float $currentPrice, Context $context): bool
    {
        $mailTemplateTypeId = $this->connection->fetchOne(
            'SELECT id FROM mail_template_type WHERE technical_name = :technicalName LIMIT 1',
            ['technicalName' => self::MAIL_TEMPLATE_TYPE]
        );

        if (!\is_string($mailTemplateTypeId) || $mailTemplateTypeId === '') {
            return false;
        }

        $fullName = trim(((string) ($customer['firstName'] ?? '')) . ' ' . ((string) ($customer['lastName'] ?? '')));
        $recipientEmail = (string) ($customer['email'] ?? '');
        if ($recipientEmail === '') {
            return false;
        }

        $productId = (string) ($product['id'] ?? '');
        $productName = (string) ($product['translated']['name'] ?? $product['name'] ?? 'this product');
        $subject = sprintf('Price dropped: %s', $productName);
        $productUrl = $this->router->generate('frontend.detail.page', ['productId' => $productId], UrlGeneratorInterface::ABSOLUTE_URL);

        $contentPlain = sprintf(
            "Hello %s,\n\nThe price dropped for %s.\nCurrent price: %.2f\n\nView the product: %s",
            $fullName !== '' ? $fullName : $recipientEmail,
            $productName,
            $currentPrice,
            $productUrl
        );
        $contentHtml = sprintf(
            '<p>Hello %s,</p><p>The price dropped for <strong>%s</strong>.</p><p>Current price: <strong>%.2f</strong></p><p><a href="%s">View the product</a></p>',
            htmlspecialchars($fullName !== '' ? $fullName : $recipientEmail, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'),
            $currentPrice,
            htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8')
        );

        $templateData = [
            'customer' => $customer,
            'product' => $product,
            'currentPrice' => $currentPrice,
            'productUrl' => $productUrl,
        ];
        $senderName = (string) ($this->systemConfigService->get('core.basicInformation.shopName', $salesChannelId) ?? '');
        if ($senderName === '') {
            $senderName = 'Bookbytes';
        }

        $senderEmail = (string) ($this->systemConfigService->get('core.mailerSettings.senderAddress', $salesChannelId) ?? '');
        if ($senderEmail === '') {
            $senderEmail = (string) ($this->systemConfigService->get('core.basicInformation.email', $salesChannelId) ?? '');
        }
        if ($senderEmail === '') {
            $senderEmail = $recipientEmail;
        }

        try {
            $this->mailService->send([
                'recipients' => [
                    $recipientEmail => $fullName !== '' ? $fullName : $recipientEmail,
                ],
                'senderName' => $senderName,
                'senderEmail' => $senderEmail,
                'salesChannelId' => $salesChannelId,
                'mailTemplateTypeId' => $mailTemplateTypeId,
                'subject' => $subject,
                'contentPlain' => $contentPlain,
                'contentHtml' => $contentHtml,
            ], $context, $templateData);
            return true;
        } catch (\Throwable $exception) {
            $this->logger->error('Price drop mail could not be sent.', [
                'recipient' => $recipientEmail,
                'productId' => $productId,
                'error' => $this->toUtf8($exception->getMessage()),
            ]);
            return false;
        }
    }

    private function toUtf8(string $value): string
    {
        $sanitized = @iconv('UTF-8', 'UTF-8//IGNORE', $value);

        return \is_string($sanitized) && $sanitized !== '' ? $sanitized : 'mail send failed';
    }
}
