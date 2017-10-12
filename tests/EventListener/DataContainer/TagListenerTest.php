<?php

namespace Codefog\TagsBundle\Test\EventListener\DataContainer;

use Codefog\TagsBundle\EventListener\DataContainer\TagListener;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Codefog\TagsBundle\Model\TagModel;
use Codefog\TagsBundle\Tag;
use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\System;
use Contao\Versions;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

require_once __DIR__.'/../../Fixtures/Backend.php';
require_once __DIR__.'/../../Fixtures/Config.php';
require_once __DIR__.'/../../Fixtures/Controller.php';

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

    public function testAddAliasButton()
    {
        $this->requestStack
            ->method('getCurrentRequest')
            ->willReturn(new Request())
        ;

        /** @var DataContainer $dataContainer */
        $dataContainer = $this->createMock(DataContainer::class);

        $buttons = ['save' => 'markup'];
        $buttons = $this->tagListener->addAliasButton($buttons, $dataContainer);

        static::assertArrayHasKey('alias', $buttons);
        static::assertArrayHasKey('save', $buttons);
        static::assertSame('markup', $buttons['save']);
    }

    public function testAddAliasButtonProcess()
    {
        // Set up the callbacks
        $callback = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateAlias'])
            ->getMock()
        ;

        $callback
            ->method('generateAlias')
            ->willReturn('foobar')
        ;

        $GLOBALS['TL_DCA']['tl_cfg_tag']['fields']['alias']['save_callback'] = [
            ['callback', 'generateAlias'],
            function () {
                return 'foobar';
            }
        ];

        // Set up the data container
        $dataContainer = $this
            ->getMockBuilder(DataContainer::class)
            ->setMethods(['__get', 'getPalette', 'save'])
            ->getMock()
        ;

        $dataContainer
            ->method('__get')
            ->willReturnCallback(function ($arg) {
                switch ($arg) {
                    case 'id':
                        return 123;
                    case 'activeRecord':
                        return new \stdClass();
                    case 'table':
                        return 'tl_cfg_tag';
                }
            })
        ;

        // Set tup the versions
        $versions = $this
            ->getMockBuilder(Versions::class)
            ->disableOriginalConstructor()
            ->setMethods(['initialize', 'create'])
            ->getMock()
        ;

        // Set up the request
        $request = new Request();
        $request->request->set('FORM_SUBMIT', 'tl_select');
        $request->request->set('alias', 1);

        $this->requestStack
            ->method('getCurrentRequest')
            ->willReturn($request)
        ;

        // Set up the session
        $this->session
            ->method('all')
            ->willReturn(['CURRENT' => ['IDS' => []]])
        ;

        // Set up the controller adapter
        $controllerAdapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['redirect'])
            ->getMock()
        ;

        $controllerAdapter
            ->method('redirect')
            ->willReturnCallback(function ($url) {
                throw new RedirectResponseException($url);
            });
        ;

        // Set up the system adapter
        $systemAdapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['importStatic', 'getReferer'])
            ->getMock()
        ;

        $systemAdapter
            ->method('importStatic')
            ->willReturn($callback)
        ;

        $systemAdapter
            ->method('getReferer')
            ->willReturn('http://domain.tld/contao')
        ;

        // Set up the database connection
        $dbRegistry = [];

        $this->connection
            ->method('update')
            ->willReturnCallback(function ($table, $values, $identifier) use (&$dbRegistry) {
                $dbRegistry[$table][$identifier['id']] = $values['alias'];
            })
        ;

        // Set up the tag adapter
        $tagModelNoUpdate = new \stdClass();
        $tagModelNoUpdate->id = 123;
        $tagModelNoUpdate->alias = 'foobar';

        $tagModelUpdate = new \stdClass();
        $tagModelUpdate->id = 456;
        $tagModelUpdate->alias = 'foobaz';

        $tagAdapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['findMultipleByIds'])
            ->getMock()
        ;

        $tagAdapter
            ->method('findMultipleByIds')
            ->willReturn([$tagModelNoUpdate, $tagModelUpdate])
        ;

        // Set up the framework
        $this->framework
            ->method('createInstance')
            ->willReturn($versions)
        ;

        $this->framework
            ->method('getAdapter')
            ->willReturnCallback(function ($class) use ($controllerAdapter, $systemAdapter, $tagAdapter) {
                switch ($class) {
                    case Controller::class:
                        return $controllerAdapter;
                    case System::class:
                        return $systemAdapter;
                    case TagModel::class:
                        return $tagAdapter;
                }
            });
        ;

        /** @var DataContainer $dataContainer */
        try {
            $this->tagListener->addAliasButton([], $dataContainer);
        } catch (RedirectResponseException $e) {
            /** @var RedirectResponse $response */
            $response = $e->getResponse();

            static::assertSame('http://domain.tld/contao', $response->getTargetUrl());
            static::assertArrayHasKey('tl_cfg_tag', $dbRegistry);
            static::assertSame([456 => 'foobar'], $dbRegistry['tl_cfg_tag']);
        }
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
