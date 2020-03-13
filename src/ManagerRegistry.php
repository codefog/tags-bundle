<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
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
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Add the manager.
     */
    public function add(ManagerInterface $manager, string $name): void
    {
        $this->managers[$name] = $manager;
    }

    /**
     * Get the manager.
     *
     * @throws \InvalidArgumentException
     */
    public function get(string $name): ManagerInterface
    {
        if (!\array_key_exists($name, $this->managers)) {
            throw new \InvalidArgumentException(sprintf('The manager "%s" does not exist', $name));
        }

        return $this->managers[$name];
    }

    /**
     * Get the names.
     */
    public function getNames(): array
    {
        return array_keys($this->managers);
    }
}
