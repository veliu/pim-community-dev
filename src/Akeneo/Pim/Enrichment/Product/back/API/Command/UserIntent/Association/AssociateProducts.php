<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association;

use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * The former associated products that are not defined in this object will stay associated.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociateProducts implements AssociationUserIntent
{// Duplicate all association user intents to handle uuids
    /**
     * @param array<string | UuidInterface> $productIdentifiersOrUuids
     */
    public function __construct(
        private string $associationType,
        private array $productIdentifiersOrUuids,
    ) {
        Assert::notEmpty($this->productIdentifiersOrUuids);
        // TODO: how to check both string and uuids?
        //Assert::allStringNotEmpty($productIdentifiers);
        Assert::stringNotEmpty($associationType);
    }

    public function associationType(): string
    {
        return $this->associationType;
    }

    /**
     * @return array<string>
     */
    public function productIdentifiersOrUuids(): array
    {
        return $this->productIdentifiersOrUuids;
    }
}
