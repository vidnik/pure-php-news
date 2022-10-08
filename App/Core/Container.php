<?php

namespace App\Core;


use App\Core\Exceptions\Container\EntityNotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private array $entries = [];

    public function get(string $id)
    {
        if (!$this->has($id)){
            throw new EntityNotFoundException('There is no class "'.$id.'" in container.');
        }

        $entry = $this->entries[$id];

        return $entry($this);
    }

    public function has(string $id): bool
    {
        return isset($this->entries[$id]);
    }

    public function set(string $id, callable $concrete){
        $this->entries[$id] = $concrete;
    }
}