<?php declare(strict_types=1);

namespace PriceDropAlerts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1777530400BackInStockMailTemplate extends MigrationStep
{
    private const MAIL_TYPE = 'bookbytes_back_in_stock_alert';

    public function getCreationTimestamp(): int
    {
        return 1777530400;
    }

    public function update(Connection $connection): void
    {
        $mailTemplateTypeId = Uuid::randomBytes();

        $connection->executeStatement(
            'INSERT INTO `mail_template_type` (`id`, `technical_name`, `available_entities`, `created_at`)
             VALUES (:id, :technicalName, :entities, :createdAt)
             ON DUPLICATE KEY UPDATE `technical_name` = `technical_name`',
            [
                'id' => $mailTemplateTypeId,
                'technicalName' => self::MAIL_TYPE,
                'entities' => json_encode(['customer' => 'customer', 'product' => 'product'], \JSON_THROW_ON_ERROR),
                'createdAt' => (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );

        $mailTemplateTypeId = $connection->fetchOne(
            'SELECT id FROM mail_template_type WHERE technical_name = :technicalName LIMIT 1',
            ['technicalName' => self::MAIL_TYPE]
        );

        if (!\is_string($mailTemplateTypeId) || $mailTemplateTypeId === '') {
            return;
        }

        $existingTemplateId = $connection->fetchOne(
            'SELECT id FROM mail_template WHERE mail_template_type_id = :mailTemplateTypeId LIMIT 1',
            ['mailTemplateTypeId' => $mailTemplateTypeId]
        );

        if ($existingTemplateId) {
            return;
        }

        $templateId = Uuid::randomBytes();
        $languageEn = $this->getLanguageIdByLocale($connection, 'en-GB');
        $languageDe = $this->getLanguageIdByLocale($connection, 'de-DE');

        $connection->insert('mail_template', [
            'id' => $templateId,
            'mail_template_type_id' => $mailTemplateTypeId,
            'system_default' => 0,
            'created_at' => (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        foreach (array_filter([$languageEn, $languageDe]) as $languageId) {
            $connection->insert('mail_template_translation', [
                'mail_template_id' => $templateId,
                'language_id' => $languageId,
                'sender_name' => '{{ salesChannel.name }}',
                'subject' => 'Back in stock: {{ product.translated.name }}',
                'description' => 'Back in stock alert mail',
                'content_html' => '<p>Hello {{ customer.firstName }} {{ customer.lastName }},</p><p><strong>{{ product.translated.name }}</strong> is back in stock.</p><p><a href="{{ productUrl }}">View the product</a></p>',
                'content_plain' => "Hello {{ customer.firstName }} {{ customer.lastName }},\n\n{{ product.translated.name }} is back in stock.\n\nView the product: {{ productUrl }}",
                'created_at' => (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function getLanguageIdByLocale(Connection $connection, string $localeCode): ?string
    {
        $languageId = $connection->fetchOne(
            'SELECT language.id
             FROM language
             INNER JOIN locale ON locale.id = language.locale_id
             WHERE locale.code = :localeCode
             LIMIT 1',
            ['localeCode' => $localeCode]
        );

        return \is_string($languageId) && $languageId !== '' ? $languageId : null;
    }
}
