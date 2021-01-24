<?php

namespace SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO;

use SteveOlotu\FeatureDocu\Annotation\FeatureDocuAnnotation;
use SteveOlotu\FeatureDocu\Exceptions\InvalidArgumentException;
use SteveOlotu\FeatureDocu\ValueObject\LivingDocumentationVO;

class ListLivingDocumentationVO extends AbstractListVO
{
    protected string $listedClass = LivingDocumentationVO::class;

    /**
     * @throws InvalidArgumentException
     */
    public function bulkPopulateWithAnnotations(ListAnnotationVO $listAnnotationVO)
    {
        foreach ($listAnnotationVO->toArray() as $annotation) {
            if ($annotation instanceof FeatureDocuAnnotation) {
                $livingDocuVO = new LivingDocumentationVO(
                    $annotation,
                    $annotation->getDescription(),
                    $annotation->getIdentifier(),
                    $annotation->getOrder(),
                    $annotation->getAnnotationClass()
                );
                $this->addOneToList($livingDocuVO);
            }
        }
    }
}
