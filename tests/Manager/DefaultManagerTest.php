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
    public function testGetAllTags()
    {
        $tag1 = new Tag('tag1', 'foo');
        $tag2 = new Tag('tag2', 'bar');

        $tags = $this->mockManager(['tl_table.tags'], ['findMultiple' => [$tag1, $tag2]])->getAllTags('tl_table.tags');

        $this->assertCount(2, $tags);
        $this->assertContains($tag1, $tags);
        $this->assertContains($tag2, $tags);
    }

    public function testGetFilteredTags()
    {
        $tag1 = new Tag('tag1', 'foo');
        $tag2 = new Tag('tag2', 'bar');

        $tags = $this->mockManager(['tl_table.tags'], ['findMultiple' => [$tag1]])->getFilteredTags(['tag1'], 'tl_table.tags');

        $this->assertCount(1, $tags);
        $this->assertContains($tag1, $tags);
        $this->assertNotContains($tag2, $tags);
    }

    public function testUpdateDcaFieldVariant1()
    {
        $dca = [];

        $this->mockManager(['tl_table.tags'])->updateDcaField('tl_table', 'tags', $dca);

        $this->assertEquals(['type' => 'haste-ManyToMany', 'load' => 'lazy', 'table' => 'tl_cfg_tag'], $dca['relation']);
        $this->assertEquals(['codefog_tags.listener.tag_manager', 'onOptionsCallback'], $dca['options_callback']);
        $this->assertEquals('tl_table.tags', $dca['eval']['tagsSource']);
        $this->assertTrue($dca['eval']['tagsCreate']);
        $this->assertEquals([['codefog_tags.listener.tag_manager', 'onFieldSaveCallback']], $dca['save_callback']);
    }

    public function testUpdateDcaFieldVariant2()
    {
        $dca = [
            'options_callback' => ['listener', 'options_method'],
            'eval' => [
                'tagsSource' => 'tl_table.tags',
            ],
            'save_callback' => [
                ['listener', 'save_method']
            ],
        ];

        $this->mockManager(['tl_table.tags'])->updateDcaField('tl_table_2', 'tags', $dca);

        $this->assertEquals(['type' => 'haste-ManyToMany', 'load' => 'lazy', 'table' => 'tl_cfg_tag'], $dca['relation']);
        $this->assertEquals(['listener', 'options_method'], $dca['options_callback']);
        $this->assertEquals('tl_table.tags', $dca['eval']['tagsSource']);
        $this->assertTrue($dca['eval']['tagsCreate']);
        $this->assertEquals([
            ['codefog_tags.listener.tag_manager', 'onFieldSaveCallback'],
            ['listener', 'save_method'],
        ], $dca['save_callback']);
    }

    public function testUpdateDcaFieldVariant3()
    {
        $dca = [
            'eval' => [
                'tagsSource' => 'tl_table.tags',
                'tagsCreate' => false,
            ],
            'sql' => ['type' => 'blob', 'notnull' => false],
        ];

        $this->mockManager(['tl_table.tags'])->updateDcaField('tl_table_2', 'tags', $dca);

        $this->assertEquals(['type' => 'blob', 'notnull' => false], $dca['sql']);
        $this->assertEquals('tl_table.tags', $dca['eval']['tagsSource']);
        $this->assertFalse($dca['eval']['tagsCreate']);
    }

    public function testSaveDcaFieldNewTags()
    {
        $tag = new Tag('bar', 'foo');
        $manager = $this->mockManager(['tl_table.tags'], ['findSingle' => null, 'createTagFromModel' => $tag]);

        $value = $manager->saveDcaField(serialize(['new-tag']), $this->createMock(DataContainer::class));
        $value = StringUtil::deserialize($value, true);

        $this->assertCount(1, $value);
        $this->assertContains('bar', $value);
    }

    public function testSaveDcaFieldNoNewTags()
    {
        $tag = new Tag('bar', 'foo');
        $manager = $this->mockManager(['tl_table.tags'], ['findSingle' => $tag]);

        $value = $manager->saveDcaField(serialize(['existing-tag']), $this->createMock(DataContainer::class));
        $value = StringUtil::deserialize($value, true);

        $this->assertCount(1, $value);
        $this->assertContains('existing-tag', $value);
    }

    public function testGetFilterOptions()
    {
        $tag1 = new Tag('tag1', 'foo');
        $tag2 = new Tag('tag2', 'bar');

        $options = $this->mockManager(['tl_table.tags'], ['findMultiple' => [$tag1, $tag2]])->getFilterOptions($this->createMock(DataContainer::class));

        $this->assertEquals(['tag1' => 'foo', 'tag2' => 'bar'], $options);
    }

    public function testGetFilterOptionsWithPredefinedTagsSource()
    {
        $tag1 = new Tag('tag1', 'foo');
        $tag2 = new Tag('tag2', 'bar');

        $GLOBALS['TL_DCA']['tl_table']['fields']['tags']['eval']['tagsSource'] = 'tl_table.tags';

        $dataContainer = $this->createMock(DataContainer::class);
        $dataContainer
            ->method('__get')
            ->willReturnMap([
                ['table', 'tl_table'],
                ['field', 'tags'],
            ])
        ;

        $options = $this->mockManager(['tl_table.tags'], ['findMultiple' => [$tag1, $tag2]])->getFilterOptions($dataContainer);

        $this->assertEquals(['tag1' => 'foo', 'tag2' => 'bar'], $options);
    }

    public function testGetSourceRecordsCountEmpty()
    {
        $manager = $this->mockManager(['tl_table.tags'], ['findSingle' => null]);

        $count = $manager->getSourceRecordsCount(['id' => 1], $this->createMock(DataContainer::class));

        $this->assertEquals(0, $count);
    }

    public function testGetSourceRecordsCount()
    {
        $tag = new Tag('bar', 'foo');
        $manager = $this->mockManager(['tl_table.tags', 'tl_table_2.tags'], ['findSingle' => $tag], ['count' => 3]);

        $count = $manager->getSourceRecordsCount(['id' => 1], $this->createMock(DataContainer::class));

        $this->assertEquals(6, $count);
    }

    public function testGetSourceRecordsCountWithPredefinedTagsSource()
    {
        $tag = new Tag('bar', 'foo');
        $manager = $this->mockManager(['tl_table.tags'], ['findSingle' => $tag], ['count' => 3]);

        $GLOBALS['TL_DCA']['tl_table']['fields']['tags']['eval']['tagsSource'] = 'tl_table.tags';

        $dataContainer = $this->createMock(DataContainer::class);
        $dataContainer
            ->method('__get')
            ->willReturnMap([
                ['table', 'tl_table'],
                ['field', 'tags'],
            ])
        ;

        $count = $manager->getSourceRecordsCount(['id' => 1], $dataContainer);

        $this->assertEquals(3, $count);
    }

    public function testGetTopTagIds()
    {
        $manager = $this->mockManager(['tl_table.tags', 'tl_table_2.tags'], ['getTopTagIds' => [1 => 1, 2 => 4, 3 => 9]]);

        $this->assertEquals([1 => 2, 2 => 8, 3 => 18], $manager->getTopTagIds());
    }

    public function testGetInsertTagValueEmpty()
    {
        $manager = $this->mockManager(['tl_table.tags'], ['findSingle' => null]);

        $this->assertEquals('', $manager->getInsertTagValue('my_manager', 'name', []));
    }

    public function testGetInsertTagValue()
    {
        $tag = new Tag('bar', 'foo');
        $tag->setData(['foobar' => 'baz']);

        $manager = $this->mockManager(['tl_table.tags'], ['findSingle' => $tag]);

        $this->assertEquals('foo', $manager->getInsertTagValue('bar', 'name', []));
        $this->assertEquals('baz', $manager->getInsertTagValue('bar', 'foobar', []));
        $this->assertEquals('', $manager->getInsertTagValue('bar', 'quux', []));
    }

    public function testGetTagFinder()
    {
        $this->assertInstanceOf(TagFinder::class, $this->mockManager(['tl_table.tags'])->getTagFinder());
    }

    public function testGetSourceFinder()
    {
        $this->assertInstanceOf(SourceFinder::class, $this->mockManager(['tl_table.tags'])->getSourceFinder());
    }

    public function testCreateTagCriteria()
    {
        $criteria = $this->mockManager(['tl_table.tags'])->createTagCriteria();

        $this->assertInstanceOf(TagCriteria::class, $criteria);
        $this->assertEquals($criteria->getName(), 'my_manager');
        $this->assertEquals($criteria->getSourceTable(), 'tl_table');
        $this->assertEquals($criteria->getSourceField(), 'tags');
    }

    public function testCreateTagCriteriaInvalidSource()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid source "tl_table_2.tags"!');

        $this->mockManager(['tl_table.tags'])->createTagCriteria('tl_table_2.tags');
    }

    public function testCreateSourceCriteria()
    {
        $criteria = $this->mockManager(['tl_table.tags'])->createSourceCriteria();

        $this->assertInstanceOf(SourceCriteria::class, $criteria);
        $this->assertEquals($criteria->getName(), 'my_manager');
        $this->assertEquals($criteria->getSourceTable(), 'tl_table');
        $this->assertEquals($criteria->getSourceField(), 'tags');
    }

    public function testCreateSourceCriteriaInvalidSource()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid source "tl_table_2.tags"!');

        $this->mockManager(['tl_table.tags'])->createSourceCriteria('tl_table_2.tags');
    }

    private function mockManager(array $sources, array $tagsFromFinder = [], array $sourcesFromFinder = []): DefaultManager
    {
        $sourceFinder = $this->createConfiguredMock(SourceFinder::class, $sourcesFromFinder);
        $tagFinder = $this->createConfiguredMock(TagFinder::class, $tagsFromFinder);

        $manager = new DefaultManager('my_manager', $sources);
        $manager->setTagFinder($tagFinder);
        $manager->setSourceFinder($sourceFinder);

        return $manager;
    }
}
