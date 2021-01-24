<?php

namespace SteveOlotu\FeatureDocu\Annotation;

/**
 * @Annotation
 * @Target({"ALL"})
 */
class LivingDocumentationA extends AbstractCoreA
{
    protected string $identifier;
    protected string $order;

    static public function getAnnotationReadme(): array
    {
        return ["Not yet implemented. Not sure if I want this. 
        Used to enforce Typehints which are not possible otherwise."];
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @param string $order
     */
    public function setOrder(string $order): void
    {
        $this->order = $order;
    }
}