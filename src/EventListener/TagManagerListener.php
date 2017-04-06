<?php

declare(strict_types = 1);

namespace Codefog\TagsBundle\EventListener;

use Codefog\TagsBundle\Manager\DcaAwareInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Contao\DataContainer;

class TagManagerListener
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
     * On load the data container
     *
     * @param string $table
     *
     * @throws \RuntimeException
     */
    public function onLoadDataContainer(string $table): void
    {
        foreach ($GLOBALS['TL_DCA'][$table]['fields'] as $name => &$field) {
            if ($field['inputType'] !== 'cfgTags') {
                continue;
            }

            $manager = $this->registry->get($field['eval']['tagsManager']);

            if ($manager instanceof DcaAwareInterface) {
                $manager->updateDcaField($field);
            }
        }
    }

    /**
     * On the field save
     *
     * @param string        $value
     * @param DataContainer $dc
     *
     * @return string
     */
    public function onFieldSave(string $value, DataContainer $dc): string
    {
        $manager = $this->registry->get($GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['tagsManager']);

        if ($manager instanceof DcaAwareInterface) {
            $value = $manager->saveDcaField($value, $dc);
        }

        return $value;
    }
}
