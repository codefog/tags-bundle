<?php

declare(strict_types=1);

namespace Codefog\TagsBundle;

use Contao\DC_Table;

class Driver extends DC_Table
{
    /**
     * @param array<string> $orderBy
     */
    public function setOrderBy(array $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    public function setFirstOrderBy(string $firstOrderBy): void
    {
        $this->firstOrderBy = $firstOrderBy;
    }
}
