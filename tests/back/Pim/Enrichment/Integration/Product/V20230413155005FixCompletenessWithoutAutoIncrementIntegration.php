<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\V20220516171405SetProductIdentifierNullableZddMigration;
use Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\V20230413155005FixCompletenessWithoutAutoIncrement;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class V20230413155005FixCompletenessWithoutAutoIncrementIntegration extends TestCase
{
    public function test_it_set_the_id_column_auto_incremental()
    {
        $this->setNotAutoIncremental();
        Assert::assertFalse($this->isColumnAutoIncremental('pim_catalog_completeness', 'id'));
        $this->runMigration();
        Assert::assertTrue($this->isColumnAutoIncremental('pim_catalog_completeness', 'id'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function runMigration(): void
    {
        $this->getMigration()->migrate();
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

        $result = $this->getConnection()->fetchOne($sql, [
            'schema' => $this->getConnection()->getDatabase(),
            'tableName' => $tableName,
            'columnName' => $columnName
        ]);

        return \intval($result) > 0;
    }

    private function setNotAutoIncremental(): void
    {
        $this->getConnection()->executeQuery(<<<SQL
        ALTER TABLE pim_catalog_completeness MODIFY COLUMN id bigint NOT NULL;
        SQL);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getMigration(): V20230413155005FixCompletenessWithoutAutoIncrement
    {
        return $this->get(V20230413155005FixCompletenessWithoutAutoIncrement::class);
    }
}
