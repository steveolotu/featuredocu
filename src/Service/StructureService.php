<?php

namespace SteveOlotu\FeatureDocu\Service;

use Doctrine\Common\Annotations\Reader;
use SteveOlotu\FeatureDocu\Annotation\AbstractCoreA;
use SteveOlotu\FeatureDocu\Annotation\InterfaceA;
use SteveOlotu\FeatureDocu\Exceptions\InvalidArgumentException;
use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListAnnotationVO;
use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListListStructureClassVO;
use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListLivingDocumentationVO;
use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListStructureClassVO;
use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListStructureMethodVO;
use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListStructurePropertyVO;
use SteveOlotu\FeatureDocu\ValueObject\Structure\StructureClassVO;
use SteveOlotu\FeatureDocu\ValueObject\Structure\StructureMethodVO;
use SteveOlotu\FeatureDocu\ValueObject\Structure\StructurePropertyVO;
use SteveOlotu\FeatureDocu\ValueObject\Structure\StructureVOInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Intl\Exception\NotImplementedException;
use UnexpectedValueException;

class StructureService
{
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

        $subPaths = PhpHelperService::customScanDir($path, false, true);
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
        $files = PhpHelperService::customScanDir($path, true, false);

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
}