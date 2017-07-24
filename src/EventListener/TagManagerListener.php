<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

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
     * On load the data container.
     *
     * @param string $table
     */
    public function onLoadDataContainer(string $table): void
    {
        if (!isset($GLOBALS['TL_DCA'][$table]['fields']) || !is_array($GLOBALS['TL_DCA'][$table]['fields'])) {
            return;
        }

        $hasTagsFields = false;

        foreach ($GLOBALS['TL_DCA'][$table]['fields'] as $name => &$field) {
            if ($field['inputType'] !== 'cfgTags') {
                continue;
            }

            $hasTagsFields = true;
            $manager = $this->registry->get($field['eval']['tagsManager']);

            if ($manager instanceof DcaAwareInterface) {
                $manager->updateDcaField($field);
            }
        }

        // Add assets for backend
        if (TL_MODE === 'BE' && $hasTagsFields) {
            $this->addAssets();
        }
    }

    /**
     * On the field save.
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

    /**
     * Add the widget assets.
     */
    private function addAssets()
    {
        $GLOBALS['TL_CSS'][] = \Haste\Util\Debug::uncompressedFile('bundles/codefogtags/selectize.min.css');
        $GLOBALS['TL_CSS'][] = \Haste\Util\Debug::uncompressedFile('bundles/codefogtags/backend.min.css');
        $GLOBALS['TL_JAVASCRIPT'][] = \Haste\Util\Debug::uncompressedFile('assets/jquery/js/jquery.min.js');
        $GLOBALS['TL_JAVASCRIPT'][] = \Haste\Util\Debug::uncompressedFile('bundles/codefogtags/selectize.min.js');
        $GLOBALS['TL_JAVASCRIPT'][] = \Haste\Util\Debug::uncompressedFile('bundles/codefogtags/widget.min.js');
        $GLOBALS['TL_JAVASCRIPT'][] = \Haste\Util\Debug::uncompressedFile('bundles/codefogtags/backend.min.js');
    }
}
