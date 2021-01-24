<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\ValueObject\Structure\StructureClassVO;

class ListStructureClassVO extends AbstractListVO
{
    protected string $listedClass = StructureClassVO::class;
}
