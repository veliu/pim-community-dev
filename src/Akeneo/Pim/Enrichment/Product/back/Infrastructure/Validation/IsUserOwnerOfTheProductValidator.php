<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Category\Domain\Query\GetCategoryCodes;
use Akeneo\Category\Domain\Query\GetOwnedCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * Check the user has own permission on the product
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IsUserOwnerOfTheProductValidator extends ConstraintValidator
{
    public function __construct(
        private GetCategoryCodes $getCategoryCodes,
        private GetOwnedCategories $getOwnedCategories
    ) {
    }

    public function validate($command, Constraint $constraint): void
    {
        Assert::isInstanceOf($command, UpsertProductCommand::class);
        Assert::isInstanceOf($constraint, IsUserOwnerOfTheProduct::class);

        try {
            $productIdentifier = ProductIdentifier::fromString($command->productIdentifier());
        } catch (\InvalidArgumentException) {
            return;
        }

        $productCategoryCodes = $this->getCategoryCodes->fromProductIdentifiers([$productIdentifier])[$productIdentifier->asString()] ?? null;
        if (null === $productCategoryCodes || [] === $productCategoryCodes) {
            return;
        }

        if ([] === $this->getOwnedCategories->forUserId($productCategoryCodes, $command->userId())) {
            $this->context->buildViolation($constraint->message)
                ->setCode((string) ViolationCode::PERMISSION)
                ->atPath('userId')
                ->addViolation();
        }
    }
}
