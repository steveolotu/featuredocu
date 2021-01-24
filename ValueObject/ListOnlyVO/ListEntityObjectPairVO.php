<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\ValueObject\Backup\ItemPairVO;

class ListEntityObjectPairVO extends AbstractListVO
{
    protected string $listedClass = ItemPairVO::class;
}
