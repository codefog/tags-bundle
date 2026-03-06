<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\EventListener\DataContainer;

use Codefog\TagsBundle\Driver;
use Codefog\TagsBundle\Manager\DcaAwareInterface;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Codefog\TagsBundle\Model\TagModel;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Slug\Slug;
use Contao\Database;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

readonly class TagListener
{
    public function __construct(
        private Connection $connection,
        private ManagerRegistry $registry,
        private RequestStack $requestStack,
        private Slug $slug,
    ) {
    }

    #[AsCallback('tl_cfg_tag', 'config.onload')]
    public function onLoadCallback(DataContainer $dc): void
    {
        if (!($dc instanceof Driver)) {
            return;
        }

        $ids = [];

        // Collect the top tags from all registries
        /** @var ManagerInterface $manager */
        foreach ($this->registry->all() as $manager) {
            if ($manager instanceof DcaAwareInterface) {
                foreach ($manager->getTopTagIds() as $id => $count) {
                    if (!isset($ids[$id])) {
                        $ids[$id] = $count;
                    } else {
                        $ids[$id] += $count;
                    }
                }
            }
        }

        // Append all other tags
        foreach ($this->connection->fetchFirstColumn("SELECT id FROM {$dc->table}") as $id) {
            if (!\array_key_exists($id, $ids)) {
                $ids[$id] = 0;
            }
        }

        /** @var AttributeBagInterface $bag */
        $bag = $this->requestStack->getSession()->getBag('contao_backend');
        $session = $bag->all();

        // Handle the sorting selection
        switch ($session['sorting'][$dc->table] ?? null) {
            case 'total_asc':
                asort($ids);
                break;

            case 'total_desc':
                arsort($ids);
                break;

            default:
                $session['sorting'][$dc->table] = null;
                $bag->replace($session);

                return;
        }

        $dc->setOrderBy([Database::getInstance()->findInSet('id', array_keys($ids))]);

        // Prevent adding an extra column to the listing
        $dc->setFirstOrderBy('name');
    }

    #[AsCallback('tl_cfg_tag', 'list.sorting.panel_callback.cfg_sort')]
    public function onPanelCallback(DataContainer $dc): string
    {
        /** @var AttributeBagInterface $bag */
        $bag = $this->requestStack->getSession()->getBag('contao_backend');
        $session = $bag->all();

        $sorting = ['_default', 'total_asc', 'total_desc'];
        $request = $this->requestStack->getCurrentRequest();

        // Store the sorting in the session
        if ('tl_filters' === $request->request->get('FORM_SUBMIT')) {
            $sort = $request->request->get('tl_sort');

            if ('_default' !== $sort && \in_array($sort, $sorting, true)) {
                $session['sorting'][$dc->table] = $sort;
            } else {
                $session['sorting'][$dc->table] = null;
            }

            $bag->replace($session);
        }

        $options = [];

        // Generate the markup options
        foreach ($sorting as $option) {
            $options[] = \sprintf(
                '<option value="%s"%s>%s</option>',
                StringUtil::specialchars($option),
                $session['sorting'][$dc->table] === $option ? ' selected="selected"' : '',
                '_default' === $option ? $GLOBALS['TL_DCA'][$dc->table]['fields'][$GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['fields'][0]]['label'][0] : $GLOBALS['TL_LANG'][$dc->table]['sortRef'][$option],
            );
        }

        return '

<div class="tl_sorting tl_subpanel">
<strong>'.$GLOBALS['TL_LANG']['MSC']['sortBy'].':</strong>
<select name="tl_sort" id="tl_sort" class="tl_select">
'.implode("\n", $options).'
</select>
</div>';
    }

    #[AsCallback('tl_cfg_tag', 'list.label.label')]
    public function onLabelCallback(array $row, string $label, DataContainer $dc, array $args): array
    {
        if ($row['source']) {
            $manager = $this->registry->get($row['source']);

            if ($manager instanceof DcaAwareInterface) {
                $args[2] = $manager->getSourceRecordsCount($row, $dc);
            }
        }

        return $args;
    }

    #[AsCallback('tl_cfg_tag', 'select.buttons')]
    public function onButtonsCallback(array $buttons, DataContainer $dc): array
    {
        $request = $this->requestStack->getCurrentRequest();

        // Generate the aliases
        if ('tl_select' === $request->request->get('FORM_SUBMIT') && $request->request->has('alias')) {
            $ids = $this->requestStack->getSession()->all()['CURRENT']['IDS'];

            // Handle each model individually
            if (null !== ($tagModels = TagModel::findMultipleByIds($ids))) {
                /** @var TagModel $tagModel */
                foreach ($tagModels as $tagModel) {
                    $dc->id = $tagModel->id;
                    $dc->activeRecord = $tagModel;

                    $alias = '';

                    // Generate new alias through save callbacks
                    foreach ($GLOBALS['TL_DCA'][$dc->table]['fields']['alias']['save_callback'] as $callback) {
                        if (\is_array($callback)) {
                            $alias = System::importStatic($callback[0])->{$callback[1]}($alias, $dc);
                        } elseif (\is_callable($callback)) {
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
                    $this->connection->update($dc->table, ['alias' => $alias], ['id' => $tagModel->id]);

                    // Create a new version
                    $versions->create();
                }
            }

            Controller::redirect(System::getReferer());
        }

        // Add the button
        $buttons['alias'] = \sprintf('<button type="submit" name="alias" id="alias" class="tl_submit" accesskey="a">%s</button> ', $GLOBALS['TL_LANG']['MSC']['aliasSelected']);

        return $buttons;
    }

    #[AsCallback('tl_cfg_tag', 'fields.source.options')]
    public function onSourceOptionsCallback(): array
    {
        $options = [];

        foreach ($this->registry->all() as $name => $manager) {
            if ($manager instanceof DcaAwareInterface) {
                $options[] = $name;
            }
        }

        return $options;
    }

    #[AsCallback('tl_cfg_tag', 'fields.alias.save')]
    public function onAliasSaveCallback(string $value, DataContainer $dc): string
    {
        if ($dc instanceof DC_Table) {
            $activeRecord = $dc->getActiveRecord();
        } else {
            $activeRecord = $dc->getCurrentRecord();
        }

        if (null === $activeRecord) {
            return $value;
        }

        $aliasExists = fn (string $alias) => false !== $this->connection->fetchOne("SELECT id FROM {$dc->table} WHERE id!=? AND alias=? AND source=?", [$dc->id, $value, $activeRecord['source']]);

        if (!$value) {
            $value = $this->slug->generate($activeRecord['headline'], duplicateCheck: $aliasExists);
        } elseif (preg_match('/^[1-9]\d*$/', $value)) {
            throw new \Exception(\sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $value));
        } elseif ($aliasExists($value)) {
            throw new \Exception(\sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
        }

        return $value;
    }
}
