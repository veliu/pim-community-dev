<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Item;

use Akeneo\Tool\Component\Batch\Job\JobProgress\ItemReaderState;

interface PausableItemReaderInterface
{
    public function getState(): ItemReaderState;
}
