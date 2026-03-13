<?php

declare(strict_types=1);

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

final class DefaultManagerTest extends ContaoTestCase
{
    public function testGetAllTags(): void
    {
        $tag1 = new Tag('tag1', 'foo');
        $tag2 = new Tag('tag2', 'bar');

        $tags = $this->mockManager(['tl_table.tags'], ['findMultiple' => [$tag1, $tag2]])->getAllTags('tl_table.tags');

        $this->assertCount(2, $tags);
        $this->assertContains($tag1, $tags);
        $this->assertContains($tag2, $tags);
    }

    public function testGetFilteredTags(): void
    {
        $tag1 = new Tag('tag1', 'foo');
        $tag2 = new Tag('tag2', 'bar');

        $tags = $this->mockManager(['tl_table.tags'], ['findMultiple' => [$tag1]])->getFilteredTags(['tag1'], 'tl_table.tags');

        $this->assertCount(1, $tags);
        $this->assertContains($tag1, $tags);
        $this->assertNotContains($tag2, $tags);
    }

    public function testUpdateDcaFieldVariant1(): void
    {
        $dca = [];

        $this->mockManager(['tl_table.tags'])->updateDcaField('tl_table', 'tags', $dca);

        $this->assertSame(['type' => 'haste-ManyToMany', 'load' => 'lazy', 'table' => 'tl_cfg_tag'], $dca['relation']);
        $this->assertSame(['codefog_tags.listener.tag_manager', 'onOptionsCallback'], $dca['options_callback']);
        $this->assertSame('tl_table.tags', $dca['eval']['tagsSource']);
        $this->assertTrue($dca['eval']['tagsCreate']);
        $this->assertSame([['codefog_tags.listener.tag_manager', 'onFieldSaveCallback']], $dca['save_callback']);
    }

    public function testUpdateDcaFieldVariant2(): void
    {
        $dca = [
            'options_callback' => ['listener', 'options_method'],
            'eval' => [
                'tagsSource' => 'tl_table.tags',
            ],
            'save_callback' => [
                ['listener', 'save_method'],
            ],
        ];

        $this->mockManager(['tl_table.tags'])->updateDcaField('tl_table_2', 'tags', $dca);

        $this->assertSame(['type' => 'haste-ManyToMany', 'load' => 'lazy', 'table' => 'tl_cfg_tag'], $dca['relation']);
        $this->assertSame(['listener', 'options_method'], $dca['options_callback']);
        $this->assertSame('tl_table.tags', $dca['eval']['tagsSource']);
        $this->assertTrue($dca['eval']['tagsCreate']);
        $this->assertSame(
            [
                ['codefog_tags.listener.tag_manager', 'onFieldSaveCallback'],
                ['listener', 'save_method'],
            ],
            $dca['save_callback'],
        );
    }

    public function testUpdateDcaFieldVariant3(): void
    {
        $dca = [
            'eval' => [
                'tagsSource' => 'tl_table.tags',
                'tagsCreate' => false,
            ],
            'sql' => ['type' => 'blob', 'notnull' => false],
        ];

        $this->mockManager(['tl_table.tags'])->updateDcaField('tl_table_2', 'tags', $dca);

        $this->assertSame(['type' => 'blob', 'notnull' => false], $dca['sql']);
        $this->assertSame('tl_table.tags', $dca['eval']['tagsSource']);
        $this->assertFalse($dca['eval']['tagsCreate']);
    }

    public function testSaveDcaFieldNewTags(): void
    {
        $GLOBALS['TL_CONFIG']['characterSet'] = 'utf-8'; // for StringUtil::generateAlias() method

        $tag = new Tag('bar', 'foo');
        $manager = $this->mockManager(['tl_table.tags'], ['findSingle' => null, 'createTagFromModel' => $tag]);

        $value = $manager->saveDcaField(serialize(['new-tag']), $this->createStub(DataContainer::class));
        $value = StringUtil::deserialize($value, true);

        $this->assertCount(1, $value);
        $this->assertContains('bar', $value);
    }

    public function testSaveDcaFieldNoNewTags(): void
    {
        $tag = new Tag('bar', 'foo');
        $manager = $this->mockManager(['tl_table.tags'], ['findSingle' => $tag]);

        $value = $manager->saveDcaField(serialize(['existing-tag']), $this->createStub(DataContainer::class));
        $value = StringUtil::deserialize($value, true);

        $this->assertCount(1, $value);
        $this->assertContains('existing-tag', $value);
    }

    public function testGetFilterOptions(): void
    {
        $tag1 = new Tag('tag1', 'foo');
        $tag2 = new Tag('tag2', 'bar');

        $options = $this->mockManager(['tl_table.tags'], ['findMultiple' => [$tag1, $tag2]])->getFilterOptions($this->createStub(DataContainer::class));

        $this->assertSame(['tag1' => 'foo', 'tag2' => 'bar'], $options);
    }

    public function testGetFilterOptionsWithPredefinedTagsSource(): void
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

        $this->assertSame(['tag1' => 'foo', 'tag2' => 'bar'], $options);
    }

    public function testGetSourceRecordsCountEmpty(): void
    {
        $manager = $this->mockManager(['tl_table.tags'], ['findSingle' => null]);

        $count = $manager->getSourceRecordsCount(['id' => 1], $this->createStub(DataContainer::class));

        $this->assertSame(0, $count);
    }

    public function testGetSourceRecordsCount(): void
    {
        $tag = new Tag('bar', 'foo');
        $manager = $this->mockManager(['tl_table.tags', 'tl_table_2.tags'], ['findSingle' => $tag], ['count' => 3]);

        $count = $manager->getSourceRecordsCount(['id' => 1], $this->createStub(DataContainer::class));

        $this->assertSame(6, $count);
    }

    public function testGetSourceRecordsCountWithPredefinedTagsSource(): void
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

        $this->assertSame(3, $count);
    }

    public function testGetTopTagIds(): void
    {
        $manager = $this->mockManager(['tl_table.tags', 'tl_table_2.tags'], ['getTopTagIds' => [1 => 1, 2 => 4, 3 => 9]]);

        $this->assertSame([1 => 2, 2 => 8, 3 => 18], $manager->getTopTagIds());
    }

    public function testGetInsertTagValueEmpty(): void
    {
        $manager = $this->mockManager(['tl_table.tags'], ['findSingle' => null]);

        $this->assertSame('', $manager->getInsertTagValue('my_manager', 'name', []));
    }

    public function testGetInsertTagValue(): void
    {
        $tag = new Tag('bar', 'foo');
        $tag->setData(['foobar' => 'baz']);

        $manager = $this->mockManager(['tl_table.tags'], ['findSingle' => $tag]);

        $this->assertSame('foo', $manager->getInsertTagValue('bar', 'name', []));
        $this->assertSame('baz', $manager->getInsertTagValue('bar', 'foobar', []));
        $this->assertSame('', $manager->getInsertTagValue('bar', 'quux', []));
    }

    public function testGetTagFinder(): void
    {
        $this->assertInstanceOf(TagFinder::class, $this->mockManager(['tl_table.tags'])->getTagFinder());
    }

    public function testGetSourceFinder(): void
    {
        $this->assertInstanceOf(SourceFinder::class, $this->mockManager(['tl_table.tags'])->getSourceFinder());
    }

    public function testCreateTagCriteria(): void
    {
        $criteria = $this->mockManager(['tl_table.tags'])->createTagCriteria();

        $this->assertInstanceOf(TagCriteria::class, $criteria);
        $this->assertSame('my_manager', $criteria->getName());
        $this->assertSame('tl_table', $criteria->getSourceTable());
        $this->assertSame('tags', $criteria->getSourceField());
    }

    public function testCreateTagCriteriaInvalidSource(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid source "tl_table_2.tags"!');

        $this->mockManager(['tl_table.tags'])->createTagCriteria('tl_table_2.tags');
    }

    public function testCreateSourceCriteria(): void
    {
        $criteria = $this->mockManager(['tl_table.tags'])->createSourceCriteria();

        $this->assertInstanceOf(SourceCriteria::class, $criteria);
        $this->assertSame('my_manager', $criteria->getName());
        $this->assertSame('tl_table', $criteria->getSourceTable());
        $this->assertSame('tags', $criteria->getSourceField());
    }

    public function testCreateSourceCriteriaInvalidSource(): void
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
