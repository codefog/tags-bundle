<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Test;

use Codefog\TagsBundle\Tag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    public function test(): void
    {
        $tag = new Tag('123', 'Foobar', ['extra' => 1]);

        $this->assertSame('123', $tag->getValue());
        $this->assertSame('Foobar', $tag->getName());
        $this->assertSame(['extra' => 1], $tag->getData());

        $tag->setValue('456');
        $this->assertSame('456', $tag->getValue());

        $tag->setName('Foobaz');
        $this->assertSame('Foobaz', $tag->getName());

        $tag->setData(['extra' => 2]);
        $this->assertSame(['extra' => 2], $tag->getData());
    }
}
