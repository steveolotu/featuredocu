<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

interface ListOnlyVOInterface
{
    public function addOneToList($object, string $key = null): void;

    public function toArray(): array;
}
