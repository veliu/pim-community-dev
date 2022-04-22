<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\CreateConnectedAppQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnectedAppQueryIntegration extends TestCase
{
    private CreateConnectedAppQuery $query;
    private ConnectionLoader $connectionLoader;
    private UserGroupLoader $userGroupLoader;
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(CreateConnectedAppQuery::class);
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->userGroupLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');
        $this->connection = $this->get('database_connection');
    }

    public function test_it_persists_an_app(): void
    {
        $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::OTHER, false);
        $this->userGroupLoader->create(['name' => 'app_123456abcdef']);

        $this->query->execute(
            new ConnectedApp(
                '86d603e6-ec67-45fa-bd79-aa8b2f649e12',
                'my app',
                ['foo', 'bar'],
                'bynder',
                'app logo',
                'app author',
                'app_123456abcdef',
                ['e-commerce'],
                false,
                'akeneo'
            )
        );

        $row = $this->fetchApp('86d603e6-ec67-45fa-bd79-aa8b2f649e12');

        Assert::assertSame([
            'id' => '86d603e6-ec67-45fa-bd79-aa8b2f649e12',
            'name' => 'my app',
            'logo' => 'app logo',
            'author' => 'app author',
            'partner' => 'akeneo',
            'categories' => '["e-commerce"]',
            'certified' => '0',
            'connection_code' => 'bynder',
            'scopes' => '["foo", "bar"]',
            'user_group_name' => 'app_123456abcdef',
            'has_outdated_scopes' => '0'
        ], $row);
    }

    private function fetchApp(string $id): ?array
    {
        $query = <<<SQL
SELECT *
FROM akeneo_connectivity_connected_app
WHERE id = :id
SQL;

        $row = $this->connection->fetchAssociative($query, [
            'id' => $id,
        ]);

        return $row ?: null;
    }
}