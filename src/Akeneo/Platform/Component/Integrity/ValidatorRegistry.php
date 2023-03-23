<?php

namespace Akeneo\Platform\Component\Integrity;

use Webmozart\Assert\Assert;

final class ValidatorRegistry
{
    /**
     * @param array<ValidatorInterface> $validators
     */
    public function __construct(
        private array $validators,
    ) {
        Assert::allIsInstanceOf($validators, ValidatorInterface::class);
    }

    public function validate(): IntegrityViolationCollection
    {

    }
}
