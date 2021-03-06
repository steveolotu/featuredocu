<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\Exceptions\NonExistentObjectException;
use SteveOlotu\FeatureDocu\ValueObject\DpaPages\DpaInterface;

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
