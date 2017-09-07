<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

class DcaExtractor
{
    public static function getInstance()
    {
        return new static();
    }

    public function getRelations()
    {
        return [];
    }
}
