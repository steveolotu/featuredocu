<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;


use SteveOlotu\FeatureDocu\Exceptions\InvalidArgumentException;
use SteveOlotu\FeatureDocu\Service\PhpHelperService;

abstract class AbstractListVO implements ListOnlyVOInterface
{
    protected string $listedClass = '';
    protected array $list = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(array $array = [])
    {
        foreach ($array as $key => $listElement) {
            $this->addOneToList($listElement, $key);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addOneToList($object, string $key = null): void
    {
        $this->validateObjectType($object);
        if (null === $key) {
            $this->list[] = $object;
        } else {
            $this->list[$key] = $object;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function validateObjectType($listElement): void
    {
        if (!PhpHelperService::isClassTypeOrImplementationOrExtension($listElement, $this->listedClass)) {
            throw new InvalidArgumentException(sprintf(
                'An object of type "%s"added to a list did not match the expected object type "%s".',
                get_class($listElement),
                $this->listedClass
            ));
        }
    }

    static private function determineFullClassName(array $array): string
    {
        $firstArrayKey = array_key_first($array);

        return get_class($array[$firstArrayKey]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addArrayToList(array $array, bool $keepKeys = false): void
    {
        if ($keepKeys) {
            foreach ($array as $key => $value) {
                $this->addOneToList($value, $key);
            }
        } else {
            foreach ($array as $value) {
                $this->addOneToList($value);
            }
        }
    }

    public function count(): int
    {
        return count($this->toArray());
    }

    public function toArray(): array
    {
        return $this->list;
    }
}
