<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\Structure\StructureClassVO;

class ListStructureClassVO extends AbstractListVO
{
    protected string $listedClass = StructureClassVO::class;
}
