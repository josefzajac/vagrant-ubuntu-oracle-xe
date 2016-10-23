<?php

namespace App\Model\Factory;

class EntityFactory
{
    public function create($className)
    {
        return new $className();
    }
}
