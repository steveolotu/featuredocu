<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\Entity\Friends\Interaction;

class ListInteractionVO extends AbstractListVO
{
    protected string $listedClass = Interaction::class;
}
