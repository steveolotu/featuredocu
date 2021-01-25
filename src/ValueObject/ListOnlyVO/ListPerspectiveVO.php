<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\Entity\Content\Perspective;

class ListPerspectiveVO extends AbstractListVO
{
    protected string $listedClass = Perspective::class;
}
