<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations;

use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class V20230413155005FixCompletenessWithoutAutoIncrement implements ZddMigration
{
    public function __construct(private Connection $connection)
    {
    }

    public function migrate(): void
    {
        if ($this->isColumnAutoIncremental('pim_catalog_completeness', 'id')) {
            return;
        }

        $this->connection->executeQuery(<<<SQL
ALTER TABLE pim_catalog_completeness MODIFY COLUMN id bigint NOT NULL AUTO_INCREMENT
SQL);
    }

    public function migrateNotZdd(): void
    {
        return;
    }

    public function getName(): string
    {
        return 'FixCompletenessTableWithoutAutoIncrement';
    }

    private function isColumnAutoIncremental(string $tableName, string $columnName): bool
    {
        $sql = <<<SQL
SELECT COUNT(*)
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = :schema
  AND TABLE_NAME = :tableName
  AND COLUMN_NAME = :columnName
  AND EXTRA like '%auto_increment%'
SQL;

        $result = $this->connection->fetchOne($sql, [
            'schema' => $this->connection->getDatabase(),
            'tableName' => $tableName,
            'columnName' => $columnName
        ]);

        return \intval($result) > 0;
    }
}
