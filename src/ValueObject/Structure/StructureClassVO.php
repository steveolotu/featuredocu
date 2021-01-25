<?php

namespace SteveOlotu\FeatureDocu\ValueObject\Structure;

use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListStructureMethodVO;
use SteveOlotu\FeatureDocu\ValueObject\ListOnlyVO\ListStructurePropertyVO;
use ReflectionClass;

class StructureClassVO extends AbstractStructureVO
{
    private string $nameShort;
    private string $pathShort;
    private string $slug;
    private ListStructurePropertyVO $listPropertyClassVO;
    private ListStructureMethodVO $listStructureMethodVO;

    public function getNameShort(): string
    {
        return $this->nameShort;
    }

    public function setNameShort(string $nameShort): void
    {
        $this->nameShort = $nameShort;
        $this->setSlug($nameShort);
    }

    public function getPathShort(): string
    {
        return $this->pathShort;
    }

    public function setPathShort(string $pathShort): void
    {
        $this->pathShort = $pathShort;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    private function setSlug(string $slug): void
    {
        $this->slug = strtolower(urlencode($slug));
    }

    public function getListStructurePropertyVO(): ListStructurePropertyVO
    {
        return $this->listPropertyClassVO;
    }

    public function setListStructurePropertyVO(ListStructurePropertyVO $listPropertyClassVO): void
    {
        $this->listPropertyClassVO = $listPropertyClassVO;
    }

    public function getListStructureMethodVO(): ListStructureMethodVO
    {
        return $this->listStructureMethodVO;
    }

    public function setListStructureMethodVO(ListStructureMethodVO $listStructureMethodVO): void
    {
        $this->listStructureMethodVO = $listStructureMethodVO;
    }

    protected function getReflectionClassName(): string
    {
        return ReflectionClass::class;
    }
}