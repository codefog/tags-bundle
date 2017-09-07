<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Test;

use Codefog\TagsBundle\Tag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    public function test()
    {
        $tag = new Tag('123', 'Foobar', ['extra' => 1]);

        static::assertEquals('123', $tag->getValue());
        static::assertEquals('Foobar', $tag->getName());
        static::assertEquals(['extra' => 1], $tag->getData());

        $tag->setValue('456');
        static::assertEquals('456', $tag->getValue());

        $tag->setName('Foobaz');
        static::assertEquals('Foobaz', $tag->getName());

        $tag->setData(['extra' => 2]);
        static::assertEquals(['extra' => 2], $tag->getData());
    }
}
