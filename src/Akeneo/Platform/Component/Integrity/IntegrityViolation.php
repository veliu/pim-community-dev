<?php

namespace Akeneo\Platform\Component\Integrity;

use Webmozart\Assert\Assert;

final class IntegrityViolation
{
    public function __construct(
        private string $subject,
        private string $reason,
    ) {
        Assert::notEmpty($subject);
        Assert::notEmpty($reason);
    }
}
