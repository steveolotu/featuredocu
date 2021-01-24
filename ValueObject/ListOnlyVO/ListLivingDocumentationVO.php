<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\Annotation\LivingDocumentationA;
use EFrane\ConsoleAdditions\FeatureDocu\Exceptions\InvalidArgumentException;
use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\LivingDocumentationVO;

class ListLivingDocumentationVO extends AbstractListVO
{
    protected string $listedClass = LivingDocumentationVO::class;

    /**
     * @throws InvalidArgumentException
     */
    public function bulkPopulateWithAnnotations(ListAnnotationVO $listAnnotationVO)
    {
        foreach ($listAnnotationVO->toArray() as $annotation) {
            if ($annotation instanceof LivingDocumentationA) {
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
