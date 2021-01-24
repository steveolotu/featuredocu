<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\Exceptions\NonExistentObjectException;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\DpaPages\DpaInterface;

class ListDpaInterfaceVO extends AbstractListVO
{
    protected string $listedClass = DpaInterface::class;

    /**
     * @throws NonExistentObjectException
     */
    public function searchBySlugAndInitialize(string $slug): DpaInterface
    {
        if (array_key_exists($slug, $this->list)) {
            $dpa = $this->list[$slug];
            $dpa->initialize();

            return $dpa;
        }

        throw new NonExistentObjectException(sprintf(
            'Unable to find the PageDocuAutomated by slug "%s".',
            $slug
        ));
    }
}
