<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\Structure\StructureClassVO;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\Structure\StructureMethodVO;

class ListStructureMethodVO extends AbstractListVO
{
    protected string $listedClass = StructureMethodVO::class;
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
