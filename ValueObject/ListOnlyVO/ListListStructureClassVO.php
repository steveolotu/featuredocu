<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\Exceptions\InvalidArgumentException;

class ListListStructureClassVO extends AbstractListVO
{
    protected string $listedClass = ListStructureClassVO::class;

    /**
     * @throws InvalidArgumentException
     */
    public function mergeLists(): ListStructureClassVO
    {
        $mergedList = new ListStructureClassVO();
        /** @var ListStructureClassVO $sublist */
        foreach ($this->list as $sublist) {
            $mergedList->addArrayToList($sublist->toArray());
        }
        return $mergedList;
    }
}
