<?php

namespace IsmayilDev\LaravelDocKit\Helper;

use ReflectionClass;
use ReflectionException;

class ArchitectureHelper
{
    /**
     * Check if an object uses a given trait.
     *
     * @throws ReflectionException
     */
    public static function classUsesTrait(string $objectClass, string $traitClass): bool
    {
        $objectReflection = new ReflectionClass($objectClass);

        return in_array($traitClass, $objectReflection->getTraits());
    }
}
