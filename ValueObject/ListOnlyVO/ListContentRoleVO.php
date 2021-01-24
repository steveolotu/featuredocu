<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\Entity\Content\ContentRole;

class ListContentRoleVO extends AbstractListVO
{
    protected string $listedClass = ContentRole::class;
}
