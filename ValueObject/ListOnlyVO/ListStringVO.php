<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\Exceptions\InvalidArgumentException;

class ListStringVO extends AbstractListVO
{
    protected string $listedClass = 'string';

    /**
     * @throws InvalidArgumentException
     */
    protected function validateObjectType($listElement): void
    {
        if (!is_string($listElement)) {
            throw new InvalidArgumentException(sprintf(
                'An object added to the "ListStringVO"-Class list is not a string.',
            ));
        }
    }
}
