<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle;

use Codefog\TagsBundle\Manager\DefaultManager;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Doctrine\DBAL\Connection;

class ManagerRegistry
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * Managers.
     *
     * @var array
     */
    private $managers = [];

    /**
     * ManagerRegistry constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Add the manager.
     *
     * @param ManagerInterface $manager
     * @param string           $alias
     */
    public function add(ManagerInterface $manager, string $alias): void
    {
        $manager->setAlias($alias);

        // @todo – change this in 3.0
        if ($manager instanceof DefaultManager) {
            $manager->setDatabase($this->db);
        }

        $this->managers[$alias] = $manager;
    }

    /**
     * Get the manager.
     *
     * @param string $alias
     *
     * @throws \InvalidArgumentException
     *
     * @return ManagerInterface
     */
    public function get(string $alias): ManagerInterface
    {
        if (!\array_key_exists($alias, $this->managers)) {
            throw new \InvalidArgumentException(\sprintf('The manager "%s" does not exist', $alias));
        }

        return $this->managers[$alias];
    }

    /**
     * Get the aliases.
     *
     * @return array
     */
    public function getAliases(): array
    {
        return \array_keys($this->managers);
    }
}
