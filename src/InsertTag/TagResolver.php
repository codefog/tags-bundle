<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\InsertTag;

use Codefog\TagsBundle\Manager\InsertTagsAwareInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;
use Contao\CoreBundle\InsertTag\InsertTagResult;
use Contao\CoreBundle\InsertTag\ResolvedInsertTag;
use Contao\CoreBundle\InsertTag\Resolver\InsertTagResolverNestedResolvedInterface;

#[AsInsertTag('tag')]
readonly class TagResolver implements InsertTagResolverNestedResolvedInterface
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    public function __invoke(ResolvedInsertTag $insertTag): InsertTagResult
    {
        $parameters = $insertTag->getParameters();

        if (3 !== \count($parameters->all())) {
            return new InsertTagResult('');
        }

        $source = $parameters->getScalar(0);
        $value = $parameters->getScalar(1);
        $property = $parameters->getScalar(2);

        $manager = $this->registry->get($source);

        if ($manager instanceof InsertTagsAwareInterface) {
            return $manager->getInsertTagValue($value, $property, $parameters->all());
        }

        return new InsertTagResult('');
    }
}
