<?php declare(strict_types=1);

namespace UserReadingInterest\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1745823000NormalizeInterestOptionsConfig extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1745823000;
    }

    public function update(Connection $connection): void
    {
        $rows = $connection->fetchAllAssociative(
            'SELECT LOWER(HEX(`id`)) AS id, `configuration_value`
             FROM `system_config`
             WHERE `configuration_key` = :configKey',
            ['configKey' => 'UserReadingInterest.config.interestOptions']
        );

        foreach ($rows as $row) {
            $decoded = json_decode((string) $row['configuration_value'], true);
            $rawValue = $decoded['_value'] ?? null;
            $normalized = $this->normalizeValue($rawValue);

            $connection->update(
                'system_config',
                [
                    'configuration_value' => json_encode(['_value' => $normalized], JSON_THROW_ON_ERROR),
                ],
                ['id' => hex2bin((string) $row['id'])],
                ['configuration_value' => ParameterType::STRING]
            );
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    /**
     * @param mixed $value
     *
     * @return list<string>
     */
    private function normalizeValue(mixed $value): array
    {
        if (\is_array($value)) {
            return array_values(array_filter(
                array_map(static fn ($item): string => trim((string) $item), $value),
                static fn ($item): bool => $item !== ''
            ));
        }

        $stringValue = trim((string) ($value ?? ''));
        if ($stringValue === '') {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn ($item): string => trim((string) $item), preg_split('/\r\n|\r|\n/', $stringValue) ?: []),
            static fn ($item): bool => $item !== ''
        ));
    }
}
