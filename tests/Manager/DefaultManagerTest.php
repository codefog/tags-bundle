<?php

namespace Codefog\TagsBundle\Test\Manager;

use Codefog\TagsBundle\Finder\SourceCriteria;
use Codefog\TagsBundle\Finder\SourceFinder;
use Codefog\TagsBundle\Finder\TagCriteria;
use Codefog\TagsBundle\Finder\TagFinder;
use Codefog\TagsBundle\Manager\DefaultManager;
use Codefog\TagsBundle\Tag;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\TestCase\ContaoTestCase;

class DefaultManagerTest extends ContaoTestCase
{
    public function testGetMultipleTags()
    {
        $tag1 = new Tag('tag1', 'foo');
        $tag2 = new Tag('tag2', 'bar');

        $tags = $this->mockManager(['findMultiple' => [$tag1, $tag2]])->getMultipleTags();

        $this->assertCount(2, $tags);
        $this->assertContains($tag1, $tags);
        $this->assertContains($tag2, $tags);
    }

    public function testUpdateDcaFieldVariant1()
    {
        $dca = [];

        $this->mockManager()->updateDcaField($dca);

        $this->assertEquals(['type' => 'haste-ManyToMany', 'load' => 'lazy', 'table' => 'tl_cfg_tag'], $dca['relation']);
        $this->assertEquals(['codefog_tags.listener.tag_manager', 'onOptionsCallback'], $dca['options_callback']);
        $this->assertEquals([['codefog_tags.listener.tag_manager', 'onFieldSave']], $dca['save_callback']);
    }

    public function testUpdateDcaFieldVariant2()
    {
        $dca = [
            'options_callback' => ['listener', 'options_method'],
            'save_callback' => [
                ['listener', 'save_method']
            ],
        ];

        $this->mockManager()->updateDcaField($dca);

        $this->assertEquals(['type' => 'haste-ManyToMany', 'load' => 'lazy', 'table' => 'tl_cfg_tag'], $dca['relation']);
        $this->assertEquals(['listener', 'options_method'], $dca['options_callback']);
        $this->assertEquals([
            ['codefog_tags.listener.tag_manager', 'onFieldSave'],
            ['listener', 'save_method'],
        ], $dca['save_callback']);
    }

    public function testSaveDcaFieldNewTags()
    {
        $tag = new Tag('bar', 'foo');
        $manager = $this->mockManager(['findSingle' => null, 'createTagFromModel' => $tag]);

        $value = $manager->saveDcaField(serialize(['new-tag']), $this->createMock(DataContainer::class));
        $value = StringUtil::deserialize($value, true);

        $this->assertCount(1, $value);
        $this->assertContains('bar', $value);
    }

    public function testSaveDcaFieldNoNewTags()
    {
        $tag = new Tag('bar', 'foo');
        $manager = $this->mockManager(['findSingle' => $tag]);

        $value = $manager->saveDcaField(serialize(['existing-tag']), $this->createMock(DataContainer::class));
        $value = StringUtil::deserialize($value, true);

        $this->assertCount(1, $value);
        $this->assertContains('existing-tag', $value);
    }

    public function testGetFilterOptions()
    {
        $tag1 = new Tag('tag1', 'foo');
        $tag2 = new Tag('tag2', 'bar');

        $options = $this->mockManager(['findMultiple' => [$tag1, $tag2]])->getFilterOptions($this->createMock(DataContainer::class));

        $this->assertEquals(['tag1' => 'foo', 'tag2' => 'bar'], $options);
    }

    public function testGetSourceRecordsCountEmpty()
    {
        $manager = $this->mockManager(['findSingle' => null]);

        $count = $manager->getSourceRecordsCount(['id' => 1], $this->createMock(DataContainer::class));

        $this->assertEquals(0, $count);
    }

    public function testGetSourceRecordsCount()
    {
        $tag = new Tag('bar', 'foo');
        $manager = $this->mockManager(['findSingle' => $tag], ['count' => 3]);

        $count = $manager->getSourceRecordsCount(['id' => 1], $this->createMock(DataContainer::class));

        $this->assertEquals(3, $count);
    }

    public function testGetInsertTagValueEmpty()
    {
        $manager = $this->mockManager(['findSingle' => null]);

        $this->assertEquals('', $manager->getInsertTagValue('my_manager', 'name', []));
    }

    public function testGetInsertTagValue()
    {
        $tag = new Tag('bar', 'foo');
        $tag->setData(['foobar' => 'baz']);

        $manager = $this->mockManager(['findSingle' => $tag]);

        $this->assertEquals('foo', $manager->getInsertTagValue('bar', 'name', []));
        $this->assertEquals('baz', $manager->getInsertTagValue('bar', 'foobar', []));
        $this->assertEquals('', $manager->getInsertTagValue('bar', 'quux', []));
    }

    public function testGetTagFinder()
    {
        $this->assertInstanceOf(TagFinder::class, $this->mockManager()->getTagFinder());
    }

    public function testGetSourceFinder()
    {
        $this->assertInstanceOf(SourceFinder::class, $this->mockManager()->getSourceFinder());
    }

    public function testCreateTagCriteria()
    {
        $criteria = $this->mockManager()->createTagCriteria();

        $this->assertInstanceOf(TagCriteria::class, $criteria);
        $this->assertEquals($criteria->getName(), 'my_manager');
        $this->assertEquals($criteria->getSourceTable(), 'tl_table');
        $this->assertEquals($criteria->getSourceField(), 'tags');
    }

    public function testCreateSourceCriteria()
    {
        $criteria = $this->mockManager()->createSourceCriteria();

        $this->assertInstanceOf(SourceCriteria::class, $criteria);
        $this->assertEquals($criteria->getName(), 'my_manager');
        $this->assertEquals($criteria->getSourceTable(), 'tl_table');
        $this->assertEquals($criteria->getSourceField(), 'tags');
    }

    private function mockManager(array $tags = [], array $sources = []): DefaultManager
    {
        $sourceFinder = $this->createConfiguredMock(SourceFinder::class, $sources);
        $tagFinder = $this->createConfiguredMock(TagFinder::class, $tags);

        $manager = new DefaultManager('my_manager', 'tl_table', 'tags');
        $manager->setTagFinder($tagFinder);
        $manager->setSourceFinder($sourceFinder);

        return $manager;
    }
}
