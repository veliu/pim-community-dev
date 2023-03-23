<?php

namespace Akeneo\Platform\Component\Integrity;

interface ValidatorInterface
{
    public function validate(): IntegrityViolationCollection;
}
