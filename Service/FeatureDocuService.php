<?php

namespace SteveOlotu\FeatureDocu;

use Doctrine\Common\Annotations\Reader;
use SteveOlotu\FeatureDocu\Annotation\AbstractCoreA;
use SteveOlotu\FeatureDocu\Annotation\LivingDocumentationA;
use SteveOlotu\FeatureDocu\Service\StructureService;
use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListListStructureClassVO;
use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListLivingDocumentationVO;
use StoryObject;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Twig\Environment;

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

    static public function test()
    {
        return 'test 123';
    }

    static public function getDocuByKey(string $key)
    {
        return new StoryObject();
    }

    public function analyze(): self
    {
        $this->setClasses($this->structureService->getListOfAllClassesInPath($this->path));

        $filterArray = [
            'getAnnotationClass' => LivingDocumentationA::class
        ];
        $this->setListLivingDocumentationVO($this->structureService->compileListOfEverythingWithAnnotation(
            $this->getClasses(),
            $filterArray
        ));

        return $this;
    }

    private function generateIdentifierArray(string $identifierString):array
    {
        $elements = explode('/', $identifierString);
        for ($i=0; $i<=3; $i++) {
            $elements[$i] = array_key_exists($i, $elements) ? $elements[$i] : '-';
        }

        return $elements;
    }

    public function getListObject()
    {
        return $this->getListLivingDocumentationVO();
    }

    public function getOutputHtml(): string
    {
        $contentArray = $this->getOutputArray();

        return $this->twig->render(
            'outputHtml.html.twig',
            ['livingDocumentationList' => $contentArray]
        );
    }


    public function getOutputArray(): array
    {
        $array = [];
        $identifiers = [];

        foreach ($this->getListLivingDocumentationVO()->toArray() as $livingDocumentationVO) {

            $annotationType = $livingDocumentationVO->getAnnotation()->getAnnotationType();
            if (AbstractCoreA::ANNOTATION_TYPES_CLASS === $annotationType) {
                $subitem = 'class';
            } elseif (AbstractCoreA::ANNOTATION_TYPES_METHOD === $annotationType) {
                $subitem = $livingDocumentationVO->getAnnotation()->getAnnotationMethod();
            } elseif (AbstractCoreA::ANNOTATION_TYPES_PROPERTY === $annotationType) {
                $subitem = $livingDocumentationVO->getAnnotation()->getAnnotationProperty();
            } else {
                throw new NotImplementedException('Annotation type undefined.');
            }

            $i = $this->generateIdentifierArray($livingDocumentationVO->getIdentifier());

            $array[$livingDocumentationVO->getIdentifier()][] = [
                'description' => $livingDocumentationVO->getDescription(),
                'class' => $livingDocumentationVO->getAnnotation()->getAnnotationClass(),
                'subitem' => $subitem,
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