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
use Contao\Database\Result;
use Contao\Database\Statement;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;

class TagListener
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var Result
     */
    private $existingAliases;

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
     * @param array $row
     * @param string $label
     * @param DataContainer $dc
     * @param array $args
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
     * Set the existing aliases
     * @param $value
     * @param DataContainer $dc
     */
    public function setExistingAliases(Result $existingAliases)
    {
        $this->existingAliases = $existingAliases;
    }

    /**
     * Retrieve existing aliases from db.
     *
     * @codeCoverageIgnore
     *
     * @param $value
     * @param DataContainer $dc
     * @return Result
     */
    public function getExistingAliases($value, DataContainer $dc): Result
    {
        if ($this->existingAliases) {
            return $this->existingAliases;
        }

        $statement = new Statement(System::getContainer()->get('database_connection'), false);

        $this->existingAliases = $statement->prepare('SELECT id FROM tl_cfg_tag WHERE alias=? AND source=?')
            ->execute($value, $dc->activeRecord->source);

        return $this->existingAliases;
    }

    /**
     * Auto-generate the tag alias if it has not been set yet.
     *
     * @param $value
     * @param DataContainer $dc
     * @return null|string
     * @throws \Exception
     */
    public function generateAlias($value, DataContainer $dc): ?string
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ($value === '') {
            $autoAlias = true;
            $value     = StringUtil::generateAlias($dc->activeRecord->name);
        }

        $existingAliases = $this->getExistingAliases($value, $dc);

        // Check whether the alias exists
        if ($existingAliases->numRows > 1 && !$autoAlias) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
        }

        // Add ID to alias
        if ($existingAliases->numRows && $autoAlias) {
            $value .= '-' . $dc->id;
        }

        $this->existingAliases = null;

        return $value;
    }

    /**
     * Automatically generate the folder URL aliases.
     *
     * @codeCoverageIgnore
     *
     * @param array $buttons
     * @param \DataContainer $dc
     *
     * @return array
     */
    public function addAliasButton($buttons, DataContainer $dc): array
    {
        // Generate the aliases
        if (Input::post('FORM_SUBMIT') === 'tl_select' && isset($_POST['alias'])) {
            /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
            $session = System::getContainer()->get('session');

            $session = $session->all();
            $ids     = $session['CURRENT']['IDS'];

            foreach ($ids as $id) {
                /** @var TagModel $adapter */
                $adapter = System::getContainer()->get('contao.framework')->getAdapter(TagModel::class);

                if (($tag = $adapter->findByCriteria(['values' => [$id]])) === null) {
                    continue;
                }

                $dc->id           = $id;
                $dc->activeRecord = $tag;

                $alias = '';

                // Generate new alias through save callbacks
                foreach ($GLOBALS['TL_DCA'][$dc->table]['fields']['alias']['save_callback'] as $callback) {
                    if (is_array($callback)) {
                        $alias = System::importStatic($callback[0])->{$callback[1]}($alias, $dc);
                    } elseif (is_callable($callback)) {
                        $alias = $callback($alias, $dc);
                    }
                }

                // The alias has not changed
                if ($alias === $tag->alias) {
                    continue;
                }

                // Initialize the version manager
                $versions = new Versions('tl_cfg_tag', $id);
                $versions->initialize();

                // Store the new alias
                $statement = new \Database\Statement(\System::getContainer()->get('database_connection'), false);
                $statement->prepare('UPDATE tl_cfg_tag SET alias=? WHERE id=?')->execute($alias, $id);

                // Create a new version
                $versions->create();
            }

            \Controller::redirect(\Controller::getReferer());
        }

        // Add the button
        $buttons['alias'] = '<button type="submit" name="alias" id="alias" class="tl_submit" accesskey="a">' . $GLOBALS['TL_LANG']['MSC']['aliasSelected'] . '</button>';

        return $buttons;
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
