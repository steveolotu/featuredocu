<?php

declare(strict_types=1);

namespace SteveOlotu\FeatureDocu\Tests\Bundle\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use SteveOlotu\FeatureDocu\Bundle\DependencyInjection\FeatureDocuExtension;

class FeatureDocuExtensionTest extends AbstractExtensionTestCase
{
    public function testExtensionCanLoad(): void
    {
        $this->load();

        self::assertTrue($this->container->hasExtension('feature_docu'));
    }

    protected function getContainerExtensions(): array
    {
        return [
            new FeatureDocuExtension(),
        ];
    }
}
