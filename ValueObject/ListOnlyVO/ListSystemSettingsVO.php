<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\Entity\System\SystemSetting;

class ListSystemSettingsVO extends AbstractListVO
{
    protected string $listedClass = SystemSetting::class;
}
