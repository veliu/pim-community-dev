<?php

namespace Akeneo\Platform\Bundle\InstallerBundle\Command;

use Symfony\Component\Console\Command\Command;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckIntegrityCommand extends Command
{
    protected static $defaultName = 'pim:integrity:check';
    protected static $defaultDescription = '';
}
