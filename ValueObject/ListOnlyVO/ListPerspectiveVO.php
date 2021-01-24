<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\Entity\Content\Perspective;

class ListPerspectiveVO extends AbstractListVO
{
    protected string $listedClass = Perspective::class;
}
