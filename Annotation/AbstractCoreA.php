<?php

namespace SteveOlotu\FeatureDocu\Annotation;

use SteveOlotu\FeatureDocu\Exceptions\InvalidArgumentException;
use SteveOlotu\FeatureDocu\Service\PhpHelperService;
use SteveOlotu\FeatureDocu\ValueObject\Structure\StructureClassVO;
use SteveOlotu\FeatureDocu\ValueObject\Structure\StructureMethodVO;
use SteveOlotu\FeatureDocu\ValueObject\Structure\StructurePropertyVO;
use SteveOlotu\FeatureDocu\ValueObject\Structure\StructureVOInterface;
use UnexpectedValueException;

abstract class AbstractCoreA implements InterfaceA, StructureVOInterface
{
    public const ANNOTATION_TYPES = [
        self::ANNOTATION_TYPES_CLASS => StructureClassVO::class,
        self::ANNOTATION_TYPES_METHOD => StructureMethodVO::class,
        self::ANNOTATION_TYPES_PROPERTY => StructurePropertyVO::class,
    ];
    public const ANNOTATION_TYPES_CLASS = 'CLASS';
    public const ANNOTATION_TYPES_METHOD = 'METHOD';
    public const ANNOTATION_TYPES_PROPERTY = 'PROPERTY';
    protected string $description = '';
    protected string $codeOfComponent;
    protected string $annotationType;
    protected ?string $annotationProperty;
    protected ?string $annotationMethod;
    protected ?string $annotationClass;

    public function __construct(array $options)
    {
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $ArrayValue => $arrayKey) {
                    $this->setKeyValuePairToProperty($arrayKey, true);
                }
            } else {
                $this->setKeyValuePairToProperty($key, $value);
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function setKeyValuePairToProperty(string $key, $value)
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;

            return;
        }
        if (property_exists($this, $value)) {
            $this->$value = true;

            return;
        }
        throw new InvalidArgumentException(sprintf(
            'Annotation property "%s" does not exist.',
            $key
        ));
    }

    abstract static function getAnnotationReadme(): array;

    static public function getDocuComponent(): ?string
    {
        return null;
    }

    static public function getDocuComponentName(): ?string
    {
        $childClass = get_called_class();
        return $childClass::getDocuComponent() ? ucfirst($childClass::getDocuComponent()) : null;
    }

    public function getAnnotationType(): string
    {
        return $this->annotationType;
    }

    public function setAnnotationType(string $annotationType): void
    {
        if (!in_array($annotationType, array_keys(self::ANNOTATION_TYPES))) {
            throw new InvalidArgumentException(sprintf('Invalid annotation type provided: %s', $annotationType));
        }
        $this->annotationType = $annotationType;
    }

    public function getAnnotationProperty(): string
    {
        return $this->annotationProperty;
    }

    public function setAnnotationProperty(string $annotationProperty): void
    {
        $this->annotationProperty = $annotationProperty;
    }

    public function getAnnotationMethod(): string
    {
        return $this->annotationMethod;
    }

    public function setAnnotationMethod(string $annotationMethod): void
    {
        $this->annotationMethod = $annotationMethod;
    }

    public function getAnnotationClass(): string
    {
        return $this->annotationClass;
    }

    public function setAnnotationClass(string $annotationClass): void
    {
        $this->annotationClass = $annotationClass;
    }

    public function isSet(string $propertyName): bool
    {
        if (!property_exists($this, $propertyName)) {
            throw new UnexpectedValueException('Property "%s" does not exist.');
        }
        return null !== $this->$propertyName;
    }

    public function get(string $propertyName): string
    {
        return $this->$propertyName;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCodeOfComponent(): string
    {
        return $this->codeOfComponent;
    }

    public function getNameShort(): string
    {
        return PhpHelperService::extractShortNameFromFullClassName($this->getClassName());
    }

    public function getClassName(): string
    {
        return self::class;
    }
}