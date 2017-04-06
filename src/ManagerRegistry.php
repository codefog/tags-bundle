<?php

declare(strict_types = 1);

namespace Codefog\TagsBundle;

use Codefog\TagsBundle\Manager\ManagerInterface;

class ManagerRegistry
{
    /**
     * Managers
     * @var array
     */
    private $managers = [];

    /**
     * Add the manager
     *
     * @param ManagerInterface $manager
     * @param string           $alias
     */
    public function add(ManagerInterface $manager, string $alias): void
    {
        $manager->setAlias($alias);
        $this->managers[$alias] = $manager;
    }

    /**
     * Get the manager
     *
     * @param string $alias
     *
     * @return ManagerInterface
     *
     * @throws \InvalidArgumentException
     */
    public function get(string $alias): ManagerInterface
    {
        if (!array_key_exists($alias, $this->managers)) {
            throw new \InvalidArgumentException(sprintf('The manager "%s" does not exist', $alias));
        }

        return $this->managers[$alias];
    }

    /**
     * Get the aliases
     *
     * @return array
     */
    public function getAliases(): array
    {
        return array_keys($this->managers);
    }
}
