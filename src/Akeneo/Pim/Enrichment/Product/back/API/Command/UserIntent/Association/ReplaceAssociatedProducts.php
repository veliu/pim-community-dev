<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association;

use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Webmozart\Assert\Assert;

/**
 * For the given association type, the former associated products that are not defined in this object
 * will be dissociated.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReplaceAssociatedProducts implements AssociationUserIntent
{
    /**
     * @param array<string | ProductUuid> $productIdentifiersOrUuids
     */
    public function __construct(
        private string $associationType,
        private array  $productIdentifiersOrUuids,
    ) {
        // TODO: how to check if stringNotEmpty or UuidsNotNull
        //Assert::allStringNotEmpty($productIdentifiersOrUuids);
        Assert::notEmpty($this->productIdentifiersOrUuids);
        Assert::stringNotEmpty($associationType);
    }

    public function associationType(): string
    {
        return $this->associationType;
    }

    /**
     * @return array<string | ProductUuid>
     */
    public function productIdentifiersOrUuids(): array
    {
        return $this->productIdentifiersOrUuids;
    }
}
