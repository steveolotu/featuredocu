<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\Annotation\InterfaceA;
use SteveOlotu\FeatureDocu\Exceptions\InvalidArgumentException;
use SteveOlotu\FeatureDocu\Service\PhpHelperService;

class ListAnnotationVO extends AbstractListVO
{
    protected string $listedClass = InterfaceA::class;

    /**
     * @throws InvalidArgumentException
     */
    public function addOneToList($object, string $key = null): void
    {
        if (!PhpHelperService::isClassTypeOrImplementationOrExtension($object, InterfaceA::class)) {
            return;
        }
        parent::addOneToList($object, $key);
    }
}
