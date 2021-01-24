<?php

namespace EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ListOnlyVO;

use EFrane\ConsoleAdditions\FeatureDocu\ValueObject\ContentLineVO;

class ListContentLineVO extends AbstractListVO
{
    protected string $listedClass = ContentLineVO::class;

    public function processMergeAndGetContent(): string
    {
        $this->processContent();
        return $this->getMergedContent();
    }

    private function getMergedContent(): string
    {
        $output = '';
        /** @var ContentLineVO $contentLineVO */
        foreach ($this->list as $contentLineVO) {
            $output .= $contentLineVO->getLineContent();
        }

        return $output;
    }

    private function processContent()
    {
        $sum = $this->count();
        for ($i=0; $i<$sum; $i++) {

            /** @var ContentLineVO $lineObject */
            $lineObject = $this->list[$i];

            $this->addHtmlListContainerTags($lineObject, $i, $sum);
            $this->list[$i] = $lineObject;
        }
    }

    private function addHtmlListContainerTags(ContentLineVO $lineObject, int $i, int $sum): void
    {
        $type = $lineObject->getType();
        $lastIteration = $sum - 1;

        if ($type->isHtmlList()) {
            if (0 === $i or $type->getName() !== $this->list[$i - 1]->getType()->getName()) {
                $content = $lineObject->getLineContent();
                $lineObject->setLineContent('<' . $type->getHtmlListTag() . '>' . $content);
            }
            if ($lastIteration === $i or $type->getName() !== $this->list[$i + 1]->getType()->getName()) {
                $content = $lineObject->getLineContent();
                $lineObject->setLineContent($content . '</' . $type->getHtmlListTag() . '>');
            }
        }
    }
}
