<?php

namespace Codefog\TagsBundle;

use Contao\DC_Table;

/**
 * @codeCoverageIgnore
 */
class Driver extends DC_Table
{
    /**
     * @param array $orderBy
     */
    public function setOrderBy(array $orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @param string $firstOrderBy
     */
    public function setFirstOrderBy(string $firstOrderBy)
    {
        $this->firstOrderBy = $firstOrderBy;
    }
}
