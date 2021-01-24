<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;


use EFrane\ConsoleAdditions\FeatureDocu\Exceptions\InvalidArgumentException;
use EFrane\ConsoleAdditions\FeatureDocu\Exceptions\WrongVariableTypeException;
use EFrane\ConsoleAdditions\FeatureDocu\Service\PhpHelperService;

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

    static public function listVOFactory(array $array): ListOnlyVOInterface
    {
        $fullClassName = self::determineFullClassName($array);
        $shortClassName = PhpHelperService::extractShortNameFromFullClassName($fullClassName);
        $listClassName = 'List' . ucfirst($shortClassName) . 'VO';

        return new $listClassName($array);
    }

    static private function determineFullClassName(array $array): string
    {
        $firstArrayKey = array_key_first($array);

        return get_class($array[$firstArrayKey]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addArrayOrArraysToList(array $arrays, bool $keepKeys = false): void
    {
        foreach ($arrays as $array) {
            $this->addArrayToList($array, $keepKeys);
        }
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

    public function getByKey(string $key)
    {
        return $this->list[$key];
    }

    public function getByKeyIfExists(?string $key)
    {
        if (!array_key_exists($key, $this->list)) {
            return null;
        } else {
            return $this->getByKey($key);
        }
    }

    public function updateElementByKey(string $key, $element): void
    {
        $this->list[$key] = $element;
    }

    public function count(): int
    {
        return count($this->toArray());
    }

    public function toArray(): array
    {
        return $this->list;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function filterByStringProperty(string $method, $value): AbstractListVO
    {
        $class = get_class($this);
        $subList = new $class();
        foreach ($this->toArray() as $key => $item) {
            PhpHelperService::ensureThatMethodExists($item, $method);
            if ($item->$method() === $value) {
                $subList->addOneToList($item, $key);
            }
        }

        return $subList;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function filterByEmptyCollectionProperty(string $method): AbstractListVO
    {
        return $this->filterByCollectionPropertyEmptiness($method, true);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function filterByCollectionPropertyEmptiness(string $method, bool $empty): AbstractListVO
    {
        $class = get_class($this);
        $subList = new $class();
        foreach ($this->toArray() as $item) {
            PhpHelperService::ensureThatMethodExists($item, $method);
            $amount = $item->$method()->count();
            if ((false === $empty && 0 < $amount) or (true === $empty && 0 === $amount)) {
                $subList->addOneToList($item);
            }
        }

        return $subList;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function filterByNonEmptyCollectionProperty(string $method): AbstractListVO
    {
        return $this->filterByCollectionPropertyEmptiness($method, false);
    }

    /**
     * @throws InvalidArgumentException
     * @throws WrongVariableTypeException
     */
    public function sortByPropertyGetter(string $propertyGetter)
    {
        $this->list = PhpHelperService::sortByPropertyGetter($propertyGetter, $this->list);
    }

    public function removeDuplicates()
    {
        $uniqueList = [];
        foreach ($this->list as $item) {
            if (!in_array($item, $uniqueList)) {
                $uniqueList[] = $item;
            }
        }
        $this->list = $uniqueList;
    }
}
