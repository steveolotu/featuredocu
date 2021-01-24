<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\ValueObject\Structure\StructureClassVO;
use SteveOlotu\FeatureDocu\ValueObject\Structure\StructurePropertyVO;

class ListStructurePropertyVO extends AbstractListVO
{
    protected string $listedClass = StructurePropertyVO::class;
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
