<?php

namespace SteveOlotu\FeatureDocu\Service;

use Doctrine\Common\Annotations\Reader;
use ReflectionException;
use SteveOlotu\FeatureDocu\Exceptions\NotImplementedYetException;
use SteveOlotu\FeatureDocu\Annotation\AbstractCoreA;
use SteveOlotu\FeatureDocu\Annotation\FeatureDocuAnnotation;
use SteveOlotu\FeatureDocu\Exceptions\InvalidArgumentException;
use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListListStructureClassVO;
use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListLivingDocumentationVO;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class FeatureDocuService
{
    private StructureService $structureService;
    private string $path;
    private ListListStructureClassVO $classes;
    private ListLivingDocumentationVO $listLivingDocumentationVO;
    private Environment $twig;

    public function __construct(string $path, Reader $reader, Environment $twig)
    {
        $this->setPath($path);
        $this->structureService = new StructureService($reader);
        $this->twig = $twig;
    }

    private function getPath(): string
    {
        return $this->path;
    }

    private function setPath(string $path): void
    {
        $this->path = $path;
    }

    static public function getDocuByKey(string $key)
    {
        // fixme not yet implemented
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function analyze(): self
    {
        $this->setClasses($this->structureService->getListOfAllClassesInPath($this->getPath()));

        $filterArray = [
            'getAnnotationClass' => FeatureDocuAnnotation::class
        ];
        $this->setListLivingDocumentationVO($this->structureService->compileListOfEverythingWithAnnotation(
            $this->getClasses(),
            $filterArray
        ));

        return $this;
    }

    public function getListObject(): ListLivingDocumentationVO
    {
        return $this->getListLivingDocumentationVO();
    }

    public function getOutputHtml(): string
    {
        $contentArray = $this->getOutputArray();

        return $this->twig->render(
            'outputHtml.html.twig',
            ['contentArray' => $contentArray]
        );
    }

    /**
     * @throws NotImplementedYetException
     */
    public function getFeatureListNestedArray(): array
    {
        $contentArray = $this->getOutputArray();

        $features = [];
        foreach ($contentArray as $key => $value) {
            $features = array_merge_recursive($features, $this->recursivelyAddElementsToFeatureList(0, $key));
        }

        return $features;
    }

    /**
     * @throws NotImplementedYetException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getFeatureListHtmlList(bool $links = true): string
    {
        return $this->twig->render(
            'featureListHtml.html.twig',
            [
                'featureList' => $this->getFeatureListNestedArray(),
                'links' => $links,
            ]
        );
    }

    private function recursivelyAddElementsToFeatureList(int $i, string $key): array
    {
        $elements = explode('/', $key, 2);
        $currentElement = $elements[0];

        $subOutput[$currentElement] = [];

        if (2 === count($elements)) {
            $subElements = $elements[1];
            $subOutput[$currentElement] = $this->recursivelyAddElementsToFeatureList($i, $subElements);
        }

        return $subOutput;
    }

    /**
     * @throws NotImplementedYetException
     */
    public function getOutputArray(): array
    {
        $array = [];
        foreach ($this->getListLivingDocumentationVO()->toArray() as $livingDocumentationVO) {

            $annotationType = $livingDocumentationVO->getAnnotation()->getAnnotationType();
            if (AbstractCoreA::ANNOTATION_TYPES_CLASS === $annotationType) {
                $subItem = 'class';
            } elseif (AbstractCoreA::ANNOTATION_TYPES_METHOD === $annotationType) {
                $subItem = $livingDocumentationVO->getAnnotation()->getAnnotationMethod();
            } elseif (AbstractCoreA::ANNOTATION_TYPES_PROPERTY === $annotationType) {
                $subItem = $livingDocumentationVO->getAnnotation()->getAnnotationProperty();
            } else {
                throw new NotImplementedYetException('Annotation type undefined.');
            }
            $array[$livingDocumentationVO->getIdentifier()][] = [
                'description' => $livingDocumentationVO->getDescription(),
                'class' => $livingDocumentationVO->getAnnotation()->getAnnotationClass(),
                'subitem' => $subItem,
                'order' => $livingDocumentationVO->getOrder(),
            ];
        }

        $orderedArray = [];
        foreach ($array as $identifier => $unorderedSubArray) {
            usort($unorderedSubArray, fn($a, $b) => strcmp($a['order'], $b['order']));
            $orderedArray[$identifier] = $unorderedSubArray;
        }

        return $orderedArray;
    }

    public function getClasses(): ListListStructureClassVO
    {
        return $this->classes;
    }

    private function setClasses(ListListStructureClassVO $classes): void
    {
        $this->classes = $classes;
    }

    private function getListLivingDocumentationVO(): ListLivingDocumentationVO
    {
        return $this->listLivingDocumentationVO;
    }

    private function setListLivingDocumentationVO(ListLivingDocumentationVO $listLivingDocumentationVO): void
    {
        $this->listLivingDocumentationVO = $listLivingDocumentationVO;
    }
}