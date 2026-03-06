<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Model;

use Contao\Model;

/**
 * @codeCoverageIgnore
 */
class TagModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_cfg_tag';
}
