<?php

declare(strict_types=1);

namespace SteveOlotu\FeatureDocu\Tests\Bundle;

use Nyholm\BundleTest\BaseBundleTestCase;
use SteveOlotu\FeatureDocu\Bundle\FeatureDocuBundle;

class FeatureDocuBundleTest extends BaseBundleTestCase
{
    protected function getBundleClass(): string
    {
        return FeatureDocuBundle::class;
    }

    public function testBundleRegistration(): void
    {
        $kernel = $this->getBootedKernel();

        self::assertInstanceOf(FeatureDocuBundle::class, $kernel->getBundle('FeatureDocuBundle'));
    }

    private function getBootedKernel(): \Nyholm\BundleTest\AppKernel
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        return $kernel;
    }
}
