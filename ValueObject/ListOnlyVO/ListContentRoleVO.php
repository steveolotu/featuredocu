<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\Entity\Content\ContentRole;

class ListContentRoleVO extends AbstractListVO
{
    protected string $listedClass = ContentRole::class;
}
