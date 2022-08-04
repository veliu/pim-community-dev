<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetProductUuids;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetProductUuids implements GetProductUuids
{
    public function __construct(private Connection $connection)
    {
    }

    public function fromIdentifier(string $identifier): ?UuidInterface
    {
        $uuid = $this->connection->fetchOne(
            'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = ?',
            [$identifier]
        );

        return false === $uuid ? null : Uuid::fromString($uuid);
    }

    public function fromIdentifiers(array $identifiers): ?array
    {
        $uuids = [];
        foreach ($identifiers as $identifier)
        {
            $uuids[] = $this->fromIdentifier($identifier);
        }

        return $uuids;
    }
}
