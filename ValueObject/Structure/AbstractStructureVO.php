<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace SteveOlotu\FeatureDocu\ValueObject\Structure;

use SteveOlotu\FeatureDocu\Exceptions\InvalidArgumentException;
use SteveOlotu\FeatureDocu\Service\PhpHelperService;
use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListAnnotationVO;
use Reflector;

abstract class AbstractStructureVO implements StructureVOInterface
{
    protected ListAnnotationVO $listAnnotationVO;
    protected $reflectionItem;
    private string $nameShort;

    public function __construct()
    {
        $this->listAnnotationVO = new ListAnnotationVO();
    }

    public function getNameShort(): string
    {
        return $this->nameShort;
    }

    public function setNameShort(string $nameShort): void
    {
        $this->nameShort = $nameShort;
    }

    public function getReflectionItem()
    {
        return $this->reflectionItem;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setReflectionItem($reflectionItem): void
    {
        PhpHelperService::ensureCorrectType($reflectionItem, $this->getReflectionClassName());
        $this->reflectionItem = $reflectionItem;
    }

    abstract protected function getReflectionClassName(): string;

    public function getListAnnotationVO(): ListAnnotationVO
    {
        return $this->listAnnotationVO;
    }

    public function setListAnnotationVO(ListAnnotationVO $listAnnotationVO): void
    {
        $this->listAnnotationVO = $listAnnotationVO;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function populateStructureClassFromReflection(Reflector $reflectionObject)
    {
        $this->setReflectionItem($reflectionObject);
        if (method_exists($reflectionObject, 'getName')) {
            $this->setNameShort($reflectionObject->getName());
        }
        if (method_exists($reflectionObject, 'getDeclaringClass')
            and method_exists($this, 'setStructureClassVO')
        ) {
            $structureClass = new StructureClassVO();
            $structureClass->populateStructureClassFromReflection($reflectionObject->getDeclaringClass());
            $this->setStructureClassVO($structureClass);
        }
    }

    public function getFullObjectClassName(): string
    {
        return $this->getReflectionItem()->getName();
    }
}