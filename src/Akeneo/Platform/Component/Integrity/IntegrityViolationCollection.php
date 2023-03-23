<?php

namespace Akeneo\Platform\Component\Integrity;

use Webmozart\Assert\Assert;

final class IntegrityViolationCollection
{
    public function __construct(
        private array $violations = [],
    ) {
        Assert::allIsInstanceOf($violations, IntegrityViolation::class);
    }

    public function addViolations(array $violations): void
    {
        Assert::allIsInstanceOf($violations, IntegrityViolation::class);
        $this->violations = [...$this->violations, $violations];
    }

    public function getViolations(): array
    {
        return $this->violations;
    }
}
