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
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\DataContainer;
use Symfony\Component\HttpFoundation\RequestStack;

class TagManagerListener
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ScopeMatcher
     */
    private $scopeMatcher;

    public function __construct(ManagerRegistry $registry, RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->registry = $registry;
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
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
        if ($hasTagsFields && ($request = $this->requestStack->getCurrentRequest()) && $this->scopeMatcher->isBackendRequest($request)) {
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
        $GLOBALS['TL_CSS'][] = 'bundles/codefogtags/selectize.min.css';
        $GLOBALS['TL_CSS'][] = 'bundles/codefogtags/backend.min.css';

        // Add the jQuery
        if (!isset($GLOBALS['TL_JAVASCRIPT']) || !preg_grep("/^assets\/jquery\/js\/jquery(\.min)?\.js$/", $GLOBALS['TL_JAVASCRIPT'])) {
            $GLOBALS['TL_JAVASCRIPT'][] = 'assets/jquery/js/jquery.min.js';
        }

        // Add jQuery UI to make the widget sortable if needed
        // @see https://jqueryui.com/download/#!version=1.12.1&themeParams=none&components=101000000100000010000000010000000000000000000000
        $GLOBALS['TL_CSS'][] = 'bundles/codefogtags/jquery-ui.min.css';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/codefogtags/jquery-ui.min.js';

        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/codefogtags/selectize.min.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/codefogtags/widget.min.js';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/codefogtags/backend.min.js';
    }
}
