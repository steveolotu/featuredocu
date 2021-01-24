<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\Annotation\InterfaceA;
use EFrane\ConsoleAdditions\FeatureDocu\Exceptions\InvalidArgumentException;
use EFrane\ConsoleAdditions\FeatureDocu\Service\PhpHelperService;

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
