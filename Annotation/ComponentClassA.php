<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class ComponentClassA extends AbstractCoreA
{
    protected bool $component = false;

    static public function getAnnotationReadme(): array
    {
        return ["Add this to each class that can be considered to be a functional component."];
    }
}