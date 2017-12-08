<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Collection;

use Codefog\TagsBundle\Tag;
use Contao\Model;
use Contao\Model\Collection;

class ModelCollection extends ArrayCollection
{
    /**
     * ModelCollection constructor.
     *
     * @param Collection|null $models
     */
    public function __construct(Collection $models = null)
    {
        if (null !== $models) {
            parent::__construct($this->createTags($models));
        }
    }

    /**
     * Create the tag from model.
     *
     * @param Model $model
     *
     * @return Tag
     */
    public static function createTagFromModel(Model $model)
    {
        return new Tag((string) $model->id, $model->name, $model->row());
    }

    /**
     * Create the tags.
     *
     * @param Collection $models
     *
     * @return array
     */
    private function createTags(Collection $models): array
    {
        $tags = [];

        /** @var Model $model */
        foreach ($models as $model) {
            $tags[] = static::createTagFromModel($model);
        }

        return $tags;
    }
}
