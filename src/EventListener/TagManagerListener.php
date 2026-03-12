<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\EventListener;

use Codefog\TagsBundle\Manager\DcaAwareInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Contao\DataContainer;

readonly class TagManagerListener
{
    public function __construct(private ManagerRegistry $registry) {
    }

    public function onLoadDataContainer(string $table): void
    {
        if (!isset($GLOBALS['TL_DCA'][$table]['fields']) || !\is_array($GLOBALS['TL_DCA'][$table]['fields'])) {
            return;
        }

        foreach ($GLOBALS['TL_DCA'][$table]['fields'] as $field => &$config) {
            if (!isset($config['inputType']) || 'cfgTags' !== $config['inputType']) {
                continue;
            }

            $manager = $this->registry->get($config['eval']['tagsManager']);

            if ($manager instanceof DcaAwareInterface) {
                $manager->updateDcaField($table, $field, $config);
            }
        }
    }

    public function onFieldSaveCallback(string $value, DataContainer $dc): string
    {
        if (null !== ($manager = $this->getManagerFromDca($dc))) {
            $value = $manager->saveDcaField($value, $dc);
        }

        return $value;
    }

    /**
     * @return array<string, string>
     */
    public function onOptionsCallback(DataContainer $dc): array
    {
        $value = [];

        if (null !== ($manager = $this->getManagerFromDca($dc))) {
            $value = $manager->getFilterOptions($dc);
        }

        return $value;
    }

    private function getManagerFromDca(DataContainer $dc): DcaAwareInterface|null
    {
        if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['tagsManager'])) {
            return null;
        }

        $manager = $this->registry->get($GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['tagsManager']);

        if ($manager instanceof DcaAwareInterface) {
            return $manager;
        }

        return null;
    }
}
