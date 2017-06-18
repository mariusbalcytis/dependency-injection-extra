<?php

namespace Maba\Component\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;

class AddTaggedByPriorityCompilerPass extends AddTaggedCompilerPass
{
    protected $priorityAttribute = 'priority';

    /**
     * @param string $priorityAttribute
     */
    public function setPriorityAttribute($priorityAttribute)
    {
        $this->priorityAttribute = $priorityAttribute;
    }

    protected function processTaggedServices(Definition $definition, $services)
    {
        $methodCalls = array();
        foreach ($services as $id => $tagAttributes) {
            try {
                $priority = $this->getAttribute($tagAttributes, $this->priorityAttribute, $id);
            } catch (\RuntimeException $exception) {
                $priority = 0;
            }
            $methodCalls[$priority][] = $this->resolveParameters($id, $tagAttributes);
        }
        ksort($methodCalls);
        foreach ($methodCalls as $parametersList) {
            foreach ($parametersList as $parameters) {
                $definition->addMethodCall($this->methodName, $parameters);
            }
        }
    }
}
