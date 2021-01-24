<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\Backup\ItemPairVO;

class ListEntityObjectPairVO extends AbstractListVO
{
    protected string $listedClass = ItemPairVO::class;
}
