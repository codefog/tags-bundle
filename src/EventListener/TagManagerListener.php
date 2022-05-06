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
use Haste\Util\Debug;

class TagManagerListener
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * TagContainer constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * On load the data container.
     */
    public function onLoadDataContainer(string $table): void
    {
        if (!isset($GLOBALS['TL_DCA'][$table]['fields']) || !\is_array($GLOBALS['TL_DCA'][$table]['fields'])) {
            return;
        }

        $hasTagsFields = false;

        foreach ($GLOBALS['TL_DCA'][$table]['fields'] as $field => &$config) {
            if (!isset($config['inputType']) || 'cfgTags' !== $config['inputType']) {
                continue;
            }

            $hasTagsFields = true;
            $manager = $this->registry->get($config['eval']['tagsManager']);

            if ($manager instanceof DcaAwareInterface) {
                $manager->updateDcaField($table, $field, $config);
            }
        }

        // Add assets for backend
        if (\defined('TL_MODE') && TL_MODE === 'BE' && $hasTagsFields) {
            $this->addAssets();
        }
    }

    /**
     * On the field save.
     */
    public function onFieldSaveCallback(string $value, DataContainer $dc): string
    {
        if (null !== ($manager = $this->getManagerFromDca($dc))) {
            $value = $manager->saveDcaField($value, $dc);
        }

        return $value;
    }

    /**
     * On options callback.
     */
    public function onOptionsCallback(DataContainer $dc): array
    {
        $value = [];

        if (null !== ($manager = $this->getManagerFromDca($dc))) {
            $value = $manager->getFilterOptions($dc);
        }

        return $value;
    }

    /**
     * Get the manager from DCA.
     */
    private function getManagerFromDca(DataContainer $dc): ?DcaAwareInterface
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

    /**
     * Add the widget assets.
     */
    private function addAssets(): void
    {
        $GLOBALS['TL_CSS'][] = Debug::uncompressedFile('bundles/codefogtags/selectize.min.css');
        $GLOBALS['TL_CSS'][] = Debug::uncompressedFile('bundles/codefogtags/backend.min.css');

        // Add the jQuery
        if (!isset($GLOBALS['TL_JAVASCRIPT']) || !preg_grep("/^assets\/jquery\/js\/jquery(\.min)?\.js$/", $GLOBALS['TL_JAVASCRIPT'])) {
            $GLOBALS['TL_JAVASCRIPT'][] = Debug::uncompressedFile('assets/jquery/js/jquery.min.js');
        }

        // Add jQuery UI to make the widget sortable if needed
        // @see https://jqueryui.com/download/#!version=1.12.1&themeParams=none&components=101000000100000010000000010000000000000000000000
        $GLOBALS['TL_CSS'][] = Debug::uncompressedFile('bundles/codefogtags/jquery-ui.min.css');
        $GLOBALS['TL_JAVASCRIPT'][] = Debug::uncompressedFile('bundles/codefogtags/jquery-ui.min.js');

        $GLOBALS['TL_JAVASCRIPT'][] = Debug::uncompressedFile('bundles/codefogtags/selectize.min.js');
        $GLOBALS['TL_JAVASCRIPT'][] = Debug::uncompressedFile('bundles/codefogtags/widget.min.js');
        $GLOBALS['TL_JAVASCRIPT'][] = Debug::uncompressedFile('bundles/codefogtags/backend.min.js');
    }
}
