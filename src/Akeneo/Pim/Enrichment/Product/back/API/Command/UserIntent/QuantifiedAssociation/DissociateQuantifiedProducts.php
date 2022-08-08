<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DissociateQuantifiedProducts implements QuantifiedAssociationUserIntent
{
    /**
     * @param string[] $productIdentifiersOrUuids
     */
    public function __construct(private string $associationType, private array $productIdentifiersOrUuids)
    {
        Assert::stringNotEmpty($associationType);
        Assert::notEmpty($productIdentifiersOrUuids);
        // TODO: check if is string not empty or uuid
        //Assert::allStringNotEmpty($productIdentifiersOrUuids);
    }

    public function associationType(): string
    {
        return $this->associationType;
    }

    /**
     * @return string[]
     */
    public function productIdentifiersOrUuids(): array
    {
        return $this->productIdentifiersOrUuids;
    }
}
