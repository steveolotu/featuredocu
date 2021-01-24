<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\Entity\System\SystemSetting;

class ListSystemSettingsVO extends AbstractListVO
{
    protected string $listedClass = SystemSetting::class;
}
