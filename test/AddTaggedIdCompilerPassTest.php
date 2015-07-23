<?php

namespace Maba\Component\DependencyInjection\Tests;

use Maba\Component\DependencyInjection\AddTaggedIdCompilerPass;

class AddTaggedIdCompilerPassTest extends AddTaggedCompilerPassTest
{
    protected function createCompilerPass($parentServiceId, $tagName, $methodName, array $attributes)
    {
        return new AddTaggedIdCompilerPass($parentServiceId, $tagName, $methodName, $attributes);
    }

    protected function buildReturn($serviceId)
    {
        return $serviceId;
    }
}
