<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\Entity\Friends\Interaction;

class ListInteractionVO extends AbstractListVO
{
    protected string $listedClass = Interaction::class;
}
