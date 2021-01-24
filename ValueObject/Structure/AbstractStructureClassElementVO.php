<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\Structure;

abstract class AbstractStructureClassElementVO extends AbstractStructureVO
{
    private StructureClassVO $structureClassVO;

    public function getStructureClassVO(): StructureClassVO
    {
        return $this->structureClassVO;
    }

    public function setStructureClassVO(StructureClassVO $structureClassVO): void
    {
        $this->structureClassVO = $structureClassVO;
    }
}