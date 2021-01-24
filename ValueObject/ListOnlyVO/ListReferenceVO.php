<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ReferenceVO;

class ListReferenceVO extends AbstractListVO
{
    protected string $listedClass = ReferenceVO::class;
}
