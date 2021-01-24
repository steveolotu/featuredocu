<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\ValueObject\Backup\EntitySetterGetterVO;

class ListEntitySetterGetterVO extends AbstractListVO
{
    protected string $listedClass = EntitySetterGetterVO::class;
}
