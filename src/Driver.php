<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

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
    public function setOrderBy(array $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @param string $firstOrderBy
     */
    public function setFirstOrderBy(string $firstOrderBy): void
    {
        $this->firstOrderBy = $firstOrderBy;
    }
}
