<?php

namespace Codefog\TagsBundle\Test;

use Codefog\TagsBundle\Tag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    public function test()
    {
        $tag = new Tag('123', 'Foobar', ['extra' => 1]);

        $this->assertEquals('123', $tag->getValue());
        $this->assertEquals('Foobar', $tag->getName());
        $this->assertEquals(['extra' => 1], $tag->getData());

        $tag->setValue('456');
        $this->assertEquals('456', $tag->getValue());

        $tag->setName('Foobaz');
        $this->assertEquals('Foobaz', $tag->getName());

        $tag->setData(['extra' => 2]);
        $this->assertEquals(['extra' => 2], $tag->getData());
    }
}
