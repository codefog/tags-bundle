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
use Codefog\TagsBundle\Model\TagModel;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TagListener
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * TagListener constructor.
     *
     * @param Connection $db
     * @param ContaoFrameworkInterface $framework
     * @param ManagerRegistry $registry
     * @param RequestStack $requestStack
     * @param SessionInterface $session
     */
    public function __construct(
        Connection $db,
        ContaoFrameworkInterface $framework,
        ManagerRegistry $registry,
        RequestStack $requestStack,
        SessionInterface $session
    ) {
        $this->db = $db;
        $this->framework = $framework;
        $this->registry = $registry;
        $this->requestStack = $requestStack;
        $this->session = $session;
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
     * Automatically generate the folder URL aliases
     *
     * @param array         $buttons
     * @param DataContainer $dc
     *
     * @return array
     */
    public function addAliasButton(array $buttons, DataContainer $dc)
    {
        $request = $this->requestStack->getCurrentRequest();

        // Generate the aliases
        if ($request->request->get('FORM_SUBMIT') === 'tl_select' && $request->request->has('alias')) {
            $ids = $this->session->all()['CURRENT']['IDS'];

            /**
             * @var Controller $controllerAdapter
             * @var System $systemAdapter
             * @var TagModel $tagAdapter
             */
            $controllerAdapter = $this->framework->getAdapter(Controller::class);
            $systemAdapter = $this->framework->getAdapter(System::class);
            $tagAdapter = $this->framework->getAdapter(TagModel::class);

            // Handle each model individually
            if (($tagModels = $tagAdapter->findMultipleByIds($ids)) !== null) {
                /** @var TagModel $tagModel */
                foreach ($tagModels as $tagModel) {
                    $dc->id = $tagModel->id;
                    $dc->activeRecord = $tagModel;

                    $alias = '';

                    // Generate new alias through save callbacks
                    foreach ($GLOBALS['TL_DCA'][$dc->table]['fields']['alias']['save_callback'] as $callback) {
                        if (is_array($callback)) {
                            $alias = $systemAdapter->importStatic($callback[0])->{$callback[1]}($alias, $dc);
                        } elseif (is_callable($callback)) {
                            $alias = $callback($alias, $dc);
                        }
                    }

                    // The alias has not changed
                    if ($alias === $tagModel->alias) {
                        continue;
                    }

                    // Initialize the version manager
                    $versions = new Versions($dc->table, $tagModel->id);
                    $versions->initialize();

                    // Store the new alias
                    $this->db->update($dc->table, ['alias' => $alias], ['id' => $tagModel->id]);

                    // Create a new version
                    $versions->create();
                }
            }

            $controllerAdapter->redirect($systemAdapter->getReferer());
        }

        // Add the button
        $buttons['alias'] = sprintf('<button type="submit" name="alias" id="alias" class="tl_submit" accesskey="a">%s</button> ', $GLOBALS['TL_LANG']['MSC']['aliasSelected']);

        return $buttons;
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
     * Generate the alias
     *
     * @param string        $value
     * @param DataContainer $dc
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function generateAlias(string $value, DataContainer $dc): string
    {
        $autoAlias = false;

        // Generate alias if there is none
        if (!$value) {
            $autoAlias = true;
            $value = standardize(StringUtil::restoreBasicEntities($dc->activeRecord->name));
        }

        $exists = $this->db->fetchAll("SELECT id FROM {$dc->table} WHERE alias=? AND source=?", [$value, $dc->activeRecord->source]);

        // Check whether the record alias exists
        if (count($exists) > 1 && !$autoAlias) {
            throw new \RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
        }

        // Add ID to alias
        if (count($exists) > 0 && $autoAlias) {
            $value .= '-' . $dc->id;
        }

        return $value;
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
