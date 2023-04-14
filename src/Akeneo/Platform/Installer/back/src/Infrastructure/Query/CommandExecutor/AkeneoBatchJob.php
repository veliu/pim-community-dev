<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Query\CommandExecutor;

use Akeneo\Platform\Installer\Domain\Query\CommandExecutor\AkeneoBatchJobInterface;

final class AkeneoBatchJob extends AbstractCommandExecutor implements AkeneoBatchJobInterface
{
    function getName(): string
    {
        return 'akeneo:batch:job';
    }
}
