<?php

namespace Codefog\TagsBundle\Test\EventListener\DataContainer;

use Codefog\TagsBundle\EventListener\DataContainer\TagListener;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Codefog\TagsBundle\Tag;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

require_once __DIR__.'/../../Fixtures/Backend.php';
require_once __DIR__.'/../../Fixtures/Config.php';

class TagListenerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    private $connection;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RequestStack
     */
    private $requestStack;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    private $session;

    /**
     * @var TagListener
     */
    private $tagListener;

    public function setUp()
    {
        $this->connection = $this->createMock(Connection::class);
        $this->framework = $this->createMock(ContaoFramework::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->session = $this->createMock(SessionInterface::class);

        $this->tagListener = new TagListener($this->connection, $this->framework, $this->managerRegistry, $this->requestStack, $this->session);
    }

    public function testInstantiation()
    {
        static::assertInstanceOf(TagListener::class, $this->tagListener);
    }

    public function testGenerateLabel()
    {
        $manager = $this->createMock(ManagerInterface::class);

        $manager
            ->method('find')
            ->willReturn(new Tag('', ''))
        ;

        $manager
            ->method('countSourceRecords')
            ->willReturn(123)
        ;

        $this->managerRegistry
            ->method('get')
            ->willReturn($manager)
        ;

        $dataContainer = $this->createMock(DataContainer::class);
        $columns = ['foo', 'bar', 0];

        /** @var DataContainer $dataContainer */
        $label = $this->tagListener->generateLabel(['source' => '', 'id' => ''], '', $dataContainer, $columns);

        static::assertCount(3, $label);
        static::assertEquals($columns[0], $label[0]);
        static::assertEquals($columns[1], $label[1]);
        static::assertEquals(123, $label[2]);
    }

    /**
     * @dataProvider generateAliasProvider
     */
    public function testGenerateAlias(array $data, $expected)
    {
        $dataContainer = $this
            ->getMockBuilder(DataContainer::class)
            ->setMethods(['__get', 'getPalette', 'save'])
            ->getMock()
        ;

        $dataContainer
            ->method('__get')
            ->willReturnCallback(function ($arg) use ($data) {
                switch ($arg) {
                    case 'id':
                        return $data['id'];
                    case 'activeRecord':
                        $activeRecord = new \stdClass();
                        $activeRecord->name = $data['name'];

                        return $activeRecord;
                    case 'table':
                        return 'tl_cfg_tag';
                }
            })
        ;

        $this->connection
            ->method('fetchAll')
            ->willReturn($data['aliases'])
        ;

        if ($data['exception'] !== null) {
            $this->expectException($data['exception']);
        }

        /** @var DataContainer $dataContainer */
        static::assertSame($expected, $this->tagListener->generateAlias($data['alias'], $dataContainer));
    }

    public function generateAliasProvider()
    {
        return [
            'Auto alias' => [
                [
                    'id' => 123,
                    'name' => 'Foo Bar',
                    'alias' => '',
                    'aliases' => [],
                    'exception' => null,
                ],
                'foo-bar',
            ],

            'Auto alias with ID' => [
                [
                    'id' => 123,
                    'name' => 'Foo Bar',
                    'alias' => '',
                    'aliases' => [['foo-bar', 'foo-baz']],
                    'exception' => null,
                ],
                'foo-bar-123',
            ],

            'Submitted alias' => [
                [
                    'id' => 123,
                    'name' => 'Foo Bar',
                    'alias' => 'foo-baz',
                    'aliases' => [],
                    'exception' => null,
                ],
                'foo-baz',
            ],

            'Alias exists' => [
                [
                    'id' => 123,
                    'name' => 'Foo Bar',
                    'alias' => 'foo-bar',
                    'aliases' => [['foo-bar'], ['foo-baz']],
                    'exception' => \RuntimeException::class,
                ],
                '',
            ],
        ];
    }

    public function testGetSources()
    {
        $this->managerRegistry
            ->method('getAliases')
            ->willReturn(['foo', 'bar'])
        ;

        static::assertEquals(['foo', 'bar'], $this->tagListener->getSources());
    }
}
