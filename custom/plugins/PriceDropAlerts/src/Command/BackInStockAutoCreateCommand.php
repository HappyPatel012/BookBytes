<?php declare(strict_types=1);

namespace PriceDropAlerts\Command;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'bookbytes:back-in-stock:auto-create',
    description: 'Create back-in-stock alerts from wishlist entries where product stock is 0 or lower.'
)]
class BackInStockAutoCreateCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SystemConfigService $systemConfigService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Run even if PriceDropAlerts.config.autoCreateBackInStockFromWishlist is disabled.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $enabled = $this->systemConfigService->get('PriceDropAlerts.config.autoCreateBackInStockFromWishlist');
        $isForced = (bool) $input->getOption('force');

        if ($enabled === false && !$isForced) {
            $io->warning('Auto-create is disabled. Enable the toggle in admin or run with --force.');
            return Command::SUCCESS;
        }

        $insertSql = <<<'SQL'
INSERT INTO `bookbytes_back_in_stock_alert`
    (`id`, `customer_id`, `product_id`, `sales_channel_id`, `last_known_stock`, `last_notified_at`, `active`, `created_at`, `updated_at`)
SELECT
    UNHEX(REPLACE(UUID(), '-', '')) AS `id`,
    cwp.`customer_id`,
    cwp.`product_id`,
    c.`sales_channel_id`,
    p.`stock` AS `last_known_stock`,
    NULL AS `last_notified_at`,
    1 AS `active`,
    :now AS `created_at`,
    NULL AS `updated_at`
FROM `customer_wishlist_product` cwp
INNER JOIN `customer` c ON c.`id` = cwp.`customer_id`
INNER JOIN `product` p ON p.`id` = cwp.`product_id`
LEFT JOIN `bookbytes_back_in_stock_alert` bsa
    ON bsa.`customer_id` = cwp.`customer_id`
    AND bsa.`product_id` = cwp.`product_id`
    AND bsa.`sales_channel_id` = c.`sales_channel_id`
WHERE p.`stock` <= 0
  AND bsa.`id` IS NULL
SQL;

        $affectedRows = $this->connection->executeStatement($insertSql, [
            'now' => (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $io->success(sprintf('Created %d back-in-stock alerts from wishlist data.', $affectedRows));

        return Command::SUCCESS;
    }
}
