<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\Service;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use EFrane\ConsoleAdditions\FeatureDocu\Annotation\AbstractCoreA;
use EFrane\ConsoleAdditions\FeatureDocu\Annotation\InterfaceA;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO\ListAnnotationVO;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO\ListListStructureClassVO;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO\ListLivingDocumentationVO;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO\ListStructureClassVO;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO\ListStructureMethodVO;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO\ListStructurePropertyVO;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\Structure\StructureClassVO;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\Structure\StructureMethodVO;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\Structure\StructurePropertyVO;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\Structure\StructureVOInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use UnexpectedValueException;

class StructureService
{
    public const RETURN_TYPE_ACTION_TYPE_DATETIME = 'datetime';
    public const RETURN_TYPE_ACTION_TYPE_DOCTRINE_COLLECTION = 'doctrineCollection';
    public const RETURN_TYPE_ACTION_TYPE_DIRECT = 'direct';
    public const RETURN_TYPE_ACTION_TYPE_ENTITY = 'entity';
    public const RETURN_TYPE_ACTION_TYPE_UUID = 'uuid';
    public const RETURN_TYPE_ACTION_TYPE_MAPPING = [
        'string' => self::RETURN_TYPE_ACTION_TYPE_DIRECT,
        'bool' => self::RETURN_TYPE_ACTION_TYPE_DIRECT,
        'Doctrine\Common\Collections\Collection' => self::RETURN_TYPE_ACTION_TYPE_DOCTRINE_COLLECTION,
        'DateTimeInterface' => self::RETURN_TYPE_ACTION_TYPE_DATETIME,
        'float' => self::RETURN_TYPE_ACTION_TYPE_DIRECT,
        'int' => self::RETURN_TYPE_ACTION_TYPE_DIRECT,
        'self' => self::RETURN_TYPE_ACTION_TYPE_ENTITY,
        'Ramsey\Uuid\UuidInterface' => self::RETURN_TYPE_ACTION_TYPE_UUID,
    ];
    private const FILE_BLACKLIST = [
        'Kernel.php',
    ];
    public const PATH_SRC = '/src';
    public const PATH_ANNOTATION = self::PATH_SRC . '/Annotation';
    public const PATH_DPA = self::PATH_SRC . '/ValueObject/DpaPages';
    public const PATH_SERVICES = self::PATH_SRC . '/Service';
    public const PATH_ENTITIES = self::PATH_SRC . '/Entity';
    public const PATH_SCRUTINY = self::PATH_SERVICES . '/Scrutiny';

    public Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function getListOfAllClassesInPath(string $path): ListListStructureClassVO
    {
        $paths = $this->getAllLegitSubPaths($path);
        foreach ($paths as $path => $placeholder) {
            $paths[$path] = $this->getClassesInPathWithoutSubdirectories($path);
        }

        return new ListListStructureClassVO($paths);
    }

    private function getAllLegitSubPaths(string $path): array
    {
        $nestedPaths = $this->recursivelyAddPathsToArray($path, []);

        return $nestedPaths;
    }

    private function recursivelyAddPathsToArray(string $path, array $pathsArray): array
    {
        $pathsArray[$path] = true;

        $subPaths = PhpHelperService::customScandir($path, false, true);
        foreach ($subPaths as $subPath) {
            $fullSubPath = $path . '/' . $subPath;
            if (is_dir($fullSubPath)) {
                $pathsArray = $this->recursivelyAddPathsToArray($fullSubPath, $pathsArray);
            }
        }

        return $pathsArray;
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function getClassesInPathWithoutSubdirectories(string $path): ListStructureClassVO
    {
        $files = $this->getPhpFilesInPath($path);

        $classes = new ListStructureClassVO();
        foreach ($files as $file) {
            if (!PhpHelperService::hasExtension($file, 'php')) {
                continue;
            }
            $class = $this->getClassFromFile($file, $path);
            $classes->addOneToList($class);
        }

        return $classes;
    }

    /**
     * @param string $path Something along the lines of '/src/ValueObject/DpaPages'. Project path is included.
     */
    private function getPhpFilesInPath(string $path): array
    {
        $files = PhpHelperService::customScandir($path, true, false);

        return $files;
    }

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function getClassFromFile(string $file, string $path): StructureClassVO
    {
        $completePath = $path . '/' . $file;
        $shortClassName = PhpHelperService::getClassNameFromFile($completePath);
        $namespace = PhpHelperService::getNamespaceFromFile($completePath);
        $completeClassName = $namespace . '\\' . $shortClassName;
        if ('' === $shortClassName) {
            throw new UnexpectedValueException(sprintf(
                'There was no class found in file "%s".',
                $file
            ));
        }
        $reflectionClass = new ReflectionClass($completeClassName);

        $class = new StructureClassVO();
        $class->setPathShort($path);
        $class->setNameShort($shortClassName);
        $class->setReflectionItem($reflectionClass);
        $class = $this->populateClassWithReflectionData($class);

        return $class;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function populateClassWithReflectionData(StructureClassVO $structureClassVO): StructureClassVO
    {
        $structureClassVO = $this->populateReflectionClassWithAnnotationData($structureClassVO);
        $structureClassVO = $this->populateReflectionClassWithMethodData($structureClassVO);
        $structureClassVO = $this->populateReflectionClassWithPropertyData($structureClassVO);

        return $structureClassVO;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function populateReflectionClassWithAnnotationData(StructureClassVO $structureClassVO): StructureClassVO
    {
        $reflectionClass = $structureClassVO->getReflectionItem();
        $classAnnotationsArray = $this->reader->getClassAnnotations($reflectionClass);
        $listAnnotationVO = new ListAnnotationVO($classAnnotationsArray);
        $listAnnotationVO = $this->setAnnotationTypeForClassAnnotations($listAnnotationVO);
        $structureClassVO->setListAnnotationVO($listAnnotationVO);

        return $structureClassVO;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function setAnnotationTypeForClassAnnotations(ListAnnotationVO $listAnnotationVO): ListAnnotationVO
    {
        $enrichedAnnotations = new ListAnnotationVO();
        /** @var AbstractCoreA $annotation */
        foreach ($listAnnotationVO->toArray() as $annotation) {
            $annotation->setAnnotationType(AbstractCoreA::ANNOTATION_TYPES_CLASS);
            $annotation->setAnnotationClass(get_class($annotation));
            $enrichedAnnotations->addOneToList($annotation);
        }

        return $enrichedAnnotations;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function populateReflectionClassWithMethodData(StructureClassVO $structureClassVO): StructureClassVO
    {
        $reflectionClass = $structureClassVO->getReflectionItem();
        $methodsArray = $reflectionClass->getMethods();
        $listStructureMethodVO = new ListStructureMethodVO();
        foreach ($methodsArray as $reflectionMethod) {
            $methodAnnotationsArray = $this->reader->getMethodAnnotations($reflectionMethod);
            $listAnnotationVO = new ListAnnotationVO($methodAnnotationsArray);

            $structureMethodVO = new StructureMethodVO();
            $structureMethodVO->setNameShort($reflectionMethod->getName());
            $listAnnotationVO = $this->setAnnotationTypeToForMethodAnnotations(
                $listAnnotationVO,
                $structureMethodVO,
                $structureClassVO
            );

            $structureMethodVO->setReflectionItem($reflectionMethod);
            $structureMethodVO->setListAnnotationVO($listAnnotationVO);

            $listStructureMethodVO->addOneToList($structureMethodVO);
        }
        $structureClassVO->setListStructureMethodVO($listStructureMethodVO);

        return $structureClassVO;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function setAnnotationTypeToForMethodAnnotations(
        ListAnnotationVO $listAnnotationVO,
        StructureMethodVO $structureMethodVO,
        StructureClassVO $structureClassVO
    ): ListAnnotationVO
    {
        $enrichedAnnotations = new ListAnnotationVO();
        /** @var AbstractCoreA $annotation */
        foreach ($listAnnotationVO->toArray() as $annotation) {
            $annotation->setAnnotationType(AbstractCoreA::ANNOTATION_TYPES_METHOD);
            $annotation->setAnnotationMethod($structureMethodVO->getNameShort());
            $annotation->setAnnotationClass($structureClassVO->getNameShort());
            $enrichedAnnotations->addOneToList($annotation);
        }

        return $enrichedAnnotations;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function populateReflectionClassWithPropertyData(StructureClassVO $structureClassVO): StructureClassVO
    {
        $reflectionClass = $structureClassVO->getReflectionItem();
        $propertiesArray = $reflectionClass->getProperties();
        $listStructurePropertyVO = new ListStructurePropertyVO();
        foreach ($propertiesArray as $reflectionProperty) {
            $propertyAnnotationsArray = $this->reader->getPropertyAnnotations($reflectionProperty);
            $listAnnotationVO = new ListAnnotationVO($propertyAnnotationsArray);

            $structurePropertyVO = new StructurePropertyVO();
            $structurePropertyVO->setNameShort($reflectionProperty->getName());
            $listAnnotationVO = $this->setAnnotationTypeToForPropertyAnnotations(
                $listAnnotationVO,
                $structurePropertyVO,
                $structureClassVO
            );

            $structurePropertyVO->setReflectionItem($reflectionProperty);
            $structurePropertyVO->setListAnnotationVO($listAnnotationVO);

            $listStructurePropertyVO->addOneToList($structurePropertyVO);
        }
        $structureClassVO->setListStructurePropertyVO($listStructurePropertyVO);

        return $structureClassVO;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function setAnnotationTypeToForPropertyAnnotations(
        ListAnnotationVO $listAnnotationVO,
        StructurePropertyVO $structurePropertyVO,
        StructureClassVO $structureClassVO
    ): ListAnnotationVO
    {
        $enrichedAnnotations = new ListAnnotationVO();
        /** @var AbstractCoreA $annotation */
        foreach ($listAnnotationVO->toArray() as $annotation) {
            $annotation->setAnnotationType(AbstractCoreA::ANNOTATION_TYPES_PROPERTY);
            $annotation->setAnnotationProperty($structurePropertyVO->getNameShort());
            $annotation->setAnnotationClass($structureClassVO->getNameShort());
            $enrichedAnnotations->addOneToList($annotation);
        }

        return $enrichedAnnotations;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function compileListOfEverythingWithAnnotation(
        ListListStructureClassVO $listListStructureClassVO,
        $filterArray
    ): ListLivingDocumentationVO
    {
        $listLivingDocumentationVO = new ListLivingDocumentationVO();

        foreach ($listListStructureClassVO->toArray() as $listStructureClassVO) {
            /** @var StructureClassVO $structureClassVO */
            foreach ($listStructureClassVO->toArray() as $structureClassVO) {
//
//                if (!$structureClassVO->getNameShort() === 'BackupController') {
//                    continue;
//                }

                $listLivingDocumentationVO->bulkPopulateWithAnnotations(
                    $this->getAnnotationOfStructureVOIfExists($structureClassVO, $filterArray)
                );

                $methodList = $structureClassVO->getListStructureMethodVO();
                /** @var StructureMethodVO $structureMethodVO */
                foreach ($methodList->toArray() as $structureMethodVO) {
                    $listLivingDocumentationVO->bulkPopulateWithAnnotations(
                        $this->getAnnotationOfStructureVOIfExists($structureMethodVO, $filterArray)
                    );
                }

                $propertyList = $structureClassVO->getListStructurePropertyVO();
                /** @var StructurePropertyVO $structurePropertyVO */
                foreach ($propertyList->toArray() as $structurePropertyVO) {
                    $listLivingDocumentationVO->bulkPopulateWithAnnotations(
                        $this->getAnnotationOfStructureVOIfExists($structurePropertyVO, $filterArray)
                    );
                }
            }
        }

        return $listLivingDocumentationVO;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getAnnotationOfStructureVOIfExists(
        StructureVOInterface $structureVO,
        array $filterArray
    ): ListAnnotationVO
    {
        /** @var ListAnnotationVO $listAnnotationsVO */
        $listAnnotationsVO = $structureVO->getListAnnotationVO();
        foreach ($listAnnotationsVO->toArray() as $annotation) {
            if ($this->filterAnnotation($annotation, $filterArray)) {
                $listAnnotationsVO->addOneToList($annotation);
            }
        }

        return $listAnnotationsVO;
    }

    private function filterAnnotation(InterfaceA $annotation, array $filterArray): bool
    {
        foreach ($filterArray as $getter => $value) {
            if (in_array($getter, get_class_methods($annotation))) {
                return $annotation->$getter() === $value;
            }
            throw new NotImplementedException(sprintf(
                'The getter "%s" does not exist for the annotation "%s".',
                $getter,
                get_class($annotation)
            ));
        }
        return false;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getAnnotationSectionInDpa(string $annotationClassName): string
    {
        $content = call_user_func($annotationClassName . '::getAnnotationReadme');
        return $this->twig->render(
            'documentation/dpaCustomContent/annotationReadmeSection.twig',
            [
                'annotationClassName' => PhpHelperService::extractShortNameFromFullClassName($annotationClassName),
                'content' => $content,
            ]
        );
    }

    /**p
     * @throws InvalidArgumentException
     */
    public function findGetterOfProperty(StructurePropertyVO $propertyVO): StructureMethodVO
    {
        $reflectionClass = $propertyVO->getReflectionItem()->getDeclaringClass();
        $propertyName = $propertyVO->getNameShort();
        foreach ($reflectionClass->getMethods() as $iteratedMethod) {
            $name = $iteratedMethod->getName();
            if (in_array($name, ['get' . ucfirst($propertyName), 'is' . ucfirst($propertyName)])) {
                $methodVO = new StructureMethodVO();
                $methodVO->populateStructureClassFromReflection($iteratedMethod);
                return $methodVO;
            }
        }
        throw new NotImplementedException(sprintf(
            'The algorithm didn\'t yield any result regarding the property getter name of "%s" in class "%s".',
            $propertyName,
            $reflectionClass->getName()
        ));
    }

    public function findGetterActionTypeOfProperty(StructureMethodVO $methodVO): string
    {
        $returnType = $methodVO->getReflectionItem()->getReturnType();
        if (null == $returnType) {
            throw new NotImplementedException(sprintf(
                'No return type set for method "%s" of class "%s".',
                $methodVO->getNameShort(),
                $methodVO->getStructureClassVO()->getNameShort()
            ));
        }
        $returnTypeString = $returnType->getName();
        if (array_key_exists($returnTypeString, self::RETURN_TYPE_ACTION_TYPE_MAPPING)) {
            return self::RETURN_TYPE_ACTION_TYPE_MAPPING[$returnTypeString];
        }
        foreach ($methodVO->getStructureClassVO()->getReflectionItem()->getInterfaces() as $interfaceReflectionClass) {
            if (BackupEntityInterface::class === $interfaceReflectionClass->getName()) {
                return self::RETURN_TYPE_ACTION_TYPE_ENTITY;
            }
        }
        throw new NotImplementedException(sprintf(
            'The action type "%s" for the method "%s" is not implemented yet, please do so.',
            $returnTypeString,
            $methodVO->getNameShort()
        ));
    }

    public function getRealClassNameEvenFromProxy(object $entityObject): string
    {
        return ClassUtils::getRealClass(get_class($entityObject));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function filterByInterface(ListStructureClassVO $list, string $interfaceClass): ListStructureClassVO
    {
        $filteredList = new ListStructureClassVO();
        foreach ($list->toArray() as $structureClassVO) {
            $interfaceNames = $structureClassVO->getReflectionItem()->getInterfaceNames();
            if (in_array($interfaceClass, $interfaceNames)) {
                $filteredList->addOneToList($structureClassVO);
            }
        }

        return $filteredList;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getClassesOfComponentBackup(ListListStructureClassVO $dpaClassesList): ListStructureClassVO
    {
        $filteredDpaClasses = new ListStructureClassVO();
        foreach ($dpaClassesList->toArray() as $path => $dpaClasses) {
            $filteredDpaClasses->addArrayToList($this->filterByClassAnnotations(
                $dpaClasses,
                BackupA::class,
                BackupA::KEY_TYPE,
            )->toArray());
        }

        return $filteredDpaClasses;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function filterByClassAnnotations(
        ListStructureClassVO $classes,
        string $annotationName,
        string $filterByAnnotationName = null,
        string $filterByAnnotationValue = null
    ): ListStructureClassVO
    {
        $classesWithAnnotations = new ListStructureClassVO();
        foreach ($classes->toArray() as $dpaClass) {
            if (
                $this->classHasAnnotation($dpaClass, $annotationName, $filterByAnnotationName, $filterByAnnotationValue)
            ) {
                $classesWithAnnotations->addOneToList($dpaClass);
            }
        }

        return $classesWithAnnotations;
    }

    private function classHasAnnotation(
        StructureClassVO $dpaClass,
        string $annotationName,
        string $filterByAnnotationName = null,
        string $filterByAnnotationValue = null
    ): bool
    {
        $annotation = $this->getClassAnnotation(
            $dpaClass->getReflectionItem(),
            $annotationName
        );
        if ($annotation instanceof InterfaceA and
            !$this->skipAnnotation($annotation, $filterByAnnotationName, $filterByAnnotationValue)
        ) {
            return true;
        }
        return false;
    }

    private function getClassAnnotation(ReflectionClass $reflectionClass, string $annotationName)
    {
        return $this->reader->getClassAnnotation(
            $reflectionClass,
            $annotationName
        );
    }

    private function skipAnnotation(
        InterfaceA $annotation,
        ?string $filterByAnnotationName,
        ?string $filterByAnnotationValue
    ): bool
    {
        // Only skip in two cases: Name-Filter is active and...
        if (null !== $filterByAnnotationName) {
            // ...and property of name is not set...
            if (!$annotation->isSet($filterByAnnotationName)){
                return true;
            }
            // ...or if the property is set, the Value-Filter is active but the value is not as expected.
            if (null !== $filterByAnnotationValue and $annotation->get($filterByAnnotationName) !== $filterByAnnotationValue){
                return true;
            }
        }
        return false;
    }
}