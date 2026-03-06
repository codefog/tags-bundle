<?php

declare(strict_types=1);

namespace Codefog\TagsBundle;

use Contao\DC_Table;

/**
 * @codeCoverageIgnore
 */
class Driver extends DC_Table
{
    public function setOrderBy(array $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    public function setFirstOrderBy(string $firstOrderBy): void
    {
        $this->firstOrderBy = $firstOrderBy;
    }
}
