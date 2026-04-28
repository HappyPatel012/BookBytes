<?php declare(strict_types=1);

namespace StudentCourse\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1745810000StudentCourse extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1745810000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('CREATE TABLE IF NOT EXISTS `student_course` (
            `id` BINARY(16) NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `instructor` VARCHAR(255) NOT NULL,
            `level` VARCHAR(100) NOT NULL,
            `description` LONGTEXT NULL,
            `start_date` DATETIME(3) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
