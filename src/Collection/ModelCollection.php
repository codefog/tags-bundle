<?php

declare(strict_types = 1);

namespace Codefog\TagsBundle\Collection;

use Codefog\TagsBundle\Tag;
use Contao\Model;
use Contao\Model\Collection;

class ModelCollection implements CollectionInterface
{
    /**
     * Tags
     * @var array
     */
    private $tags = [];

    /**
     * ModelCollection constructor.
     *
     * @param Collection|null $models
     */
    public function __construct(Collection $models = null)
    {
        if ($models !== null) {
            $this->tags = $this->createTags($models);
        }
    }

    /**
     * Create the tags
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

    /**
     * Create the tag from model
     *
     * @param Model $model
     *
     * @return Tag
     */
    public static function createTagFromModel(Model $model)
    {
        return new Tag((string)$model->id, $model->name, $model->row());
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->tags);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->tags);
    }
}
