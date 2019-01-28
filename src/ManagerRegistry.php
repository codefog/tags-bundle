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
     * @param string           $name
     */
    public function add(ManagerInterface $manager, string $name): void
    {
        $this->managers[$name] = $manager;
    }

    /**
     * Get the manager.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return ManagerInterface
     */
    public function get(string $name): ManagerInterface
    {
        if (!\array_key_exists($name, $this->managers)) {
            throw new \InvalidArgumentException(\sprintf('The manager "%s" does not exist', $name));
        }

        return $this->managers[$name];
    }

    /**
     * Get the names.
     *
     * @return array
     */
    public function getNames(): array
    {
        return \array_keys($this->managers);
    }
}
