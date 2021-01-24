<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\ValueObject\ReferenceVO;

class ListReferenceVO extends AbstractListVO
{
    protected string $listedClass = ReferenceVO::class;
}
