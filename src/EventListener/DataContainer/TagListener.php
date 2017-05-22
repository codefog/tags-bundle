<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\EventListener\DataContainer;

use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Contao\DataContainer;

class TagListener
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * TagContainer constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Generate the label.
     *
     * @param array         $row
     * @param string        $label
     * @param DataContainer $dc
     * @param array         $args
     *
     * @return array
     */
    public function generateLabel(array $row, $label, DataContainer $dc, array $args): array
    {
        $manager = $this->getManager($row['source']);

        if (($tag = $manager->find($row['id'])) !== null) {
            $args[2] = $manager->countSourceRecords($tag);
        }

        return $args;
    }

    /**
     * Get the sources.
     *
     * @return array
     */
    public function getSources(): array
    {
        return $this->registry->getAliases();
    }

    /**
     * Get the manager.
     *
     * @param string $alias
     *
     * @return ManagerInterface
     */
    private function getManager(string $alias): ManagerInterface
    {
        return $this->registry->get($alias);
    }
}
