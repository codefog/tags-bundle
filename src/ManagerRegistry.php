<?php

declare(strict_types=1);

namespace Codefog\TagsBundle;

use Codefog\TagsBundle\Manager\ManagerInterface;

class ManagerRegistry
{
    private array $managers = [];

    public function add(ManagerInterface $manager, string $name): void
    {
        $this->managers[$name] = $manager;
    }

    public function get(string $name): ManagerInterface
    {
        if (!\array_key_exists($name, $this->managers)) {
            throw new \InvalidArgumentException(\sprintf('The manager "%s" does not exist', $name));
        }

        return $this->managers[$name];
    }

    public function all(): array
    {
        return $this->managers;
    }
}
