<?php

namespace SteveOlotu\FeatureDocu\Annotation;

/**
 * @Annotation
 * @Target({"ALL"})
 */
class CodeTypehintA extends AbstractCoreA
{
    protected bool $component = false;

    static public function getAnnotationReadme(): array
    {
        return ["Not yet implemented. Not sure if I want this. 
        Used to enforce Typehints which are not possible otherwise."];
    }
}