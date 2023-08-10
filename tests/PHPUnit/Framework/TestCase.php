<?php

namespace Nadia\ElasticSearchODM\Tests\PHPUnit\Framework;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function createMockBuilderAndOnlyMethods($className, array $methods = null)
    {
        $builder = $this->getMockBuilder($className)->disableOriginalConstructor();

        if (method_exists($builder, 'onlyMethods')) {
            $builder->onlyMethods($methods);
        } else {
            $builder->setMethods($methods);
        }

        return $builder;
    }
}
