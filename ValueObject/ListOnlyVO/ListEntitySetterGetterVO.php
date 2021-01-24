<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\Backup\EntitySetterGetterVO;

class ListEntitySetterGetterVO extends AbstractListVO
{
    protected string $listedClass = EntitySetterGetterVO::class;
}
