<?php

namespace SteveOlotu\FeatureDocu\ValueObject\Structure;

use ReflectionProperty;

class StructurePropertyVO extends AbstractStructureClassElementVO
{
    protected function getReflectionClassName(): string
    {
        return ReflectionProperty::class;
    }
}