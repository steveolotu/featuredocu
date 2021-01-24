<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject;

use EFrane\ConsoleAdditions\FeatureDocu\Annotation\InterfaceA;

class LivingDocumentationVO
{
    private InterfaceA $annotation;
    private string $description;
    private string $identifier;
    private string $order;
    private string $structureVO;

    public function __construct(
        InterfaceA $annotation,
        string $description,
        string $identifier,
        string $order,
        string $structureVO
    )
    {
        $this->setDescription($description);
        $this->setIdentifier($identifier);
        $this->setOrder($order);
        $this->setOrder($order);
        $this->setStructureVO($structureVO);
        $this->setAnnotation($annotation);
    }

    public function getStructureVO(): string
    {
        return $this->structureVO;
    }

    public function setStructureVO(string $structureVO): void
    {
        $this->structureVO = $structureVO;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): void
    {
        $this->order = $order;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getAnnotation(): InterfaceA
    {
        return $this->annotation;
    }

    public function setAnnotation(InterfaceA $annotation): void
    {
        $this->annotation = $annotation;
    }
}