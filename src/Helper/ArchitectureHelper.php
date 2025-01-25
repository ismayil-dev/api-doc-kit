<?php

namespace IsmayilDev\ApiDocKit\Helper;

use ReflectionClass;
use ReflectionException;

class ArchitectureHelper
{
    /**
     * Check if an object uses a given trait.
     *
     * @throws ReflectionException
     */
    public function classUsesTrait(string $objectClass, string $traitClass): bool
    {
        $objectReflection = new ReflectionClass($objectClass);

        return in_array($traitClass, $objectReflection->getTraits());
    }
}
