<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\Structure;

use ReflectionMethod;

class StructureMethodVO extends AbstractStructureClassElementVO
{
    protected function getReflectionClassName(): string
    {
        return ReflectionMethod::class;
    }
}