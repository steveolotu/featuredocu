<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\Exceptions\InvalidArgumentException;

class ListIntegerVO extends AbstractListVO
{
    protected string $listedClass = 'int';

    /**
     * @throws InvalidArgumentException
     */
    protected function validateObjectType($listElement): void
    {
        if (!is_int($listElement)) {
            throw new InvalidArgumentException(sprintf(
                'An object added to the "ListIntegerVO"-Class list is not an int.',
            ));
        }
    }
}
