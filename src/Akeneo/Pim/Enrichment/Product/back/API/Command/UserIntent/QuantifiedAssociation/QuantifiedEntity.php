<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QuantifiedEntity
{
    public function __construct(private string | UuidInterface $entityIdentifierOrUuid, private int $quantity)
    {
        //TODO: check if string not empty or Uuid
        //Assert::stringNotEmpty($this->entityIdentifierOrUuid);
        Assert::greaterThan($this->quantity, 0);
    }

    public function entityIdentifierOrUuid(): string | UuidInterface
    {
        return $this->entityIdentifierOrUuid;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }
}
