<?php

namespace Maba\Component\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddTaggedCompilerPass implements CompilerPassInterface
{
    protected $parentServiceId;
    protected $tagName;
    protected $methodName;
    protected $attributes;

    public function __construct($parentServiceId, $tagName, $methodName, array $attributes = array())
    {
        $this->parentServiceId = $parentServiceId;
        $this->tagName = $tagName;
        $this->methodName = $methodName;
        $this->attributes = $attributes;
    }

    public function addAttribute($name)
    {
        $this->attributes[] = $name;
    }

    public function addOptionalAttribute($name, $default)
    {
        $this->attributes[$name] = $default;
    }

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->parentServiceId)) {
            return;
        }

        $definition = $container->getDefinition($this->parentServiceId);
        $services = $container->findTaggedServiceIds($this->tagName);
        $this->processTaggedServices($definition, $services);
    }

    /**
     * @param array  $attributesList
     * @param string $name
     * @param string $id
     *
     * @return mixed
     */
    protected function getAttribute($attributesList, $name, $id)
    {
        foreach ($attributesList as $attributes) {
            if (isset($attributes[$name])) {
                return $attributes[$name];
            }
        }
        throw new \RuntimeException(
            sprintf('Missing attribute %s on tag %s in %s definition', $name, $this->tagName, $id)
        );
    }

    protected function resolveServiceId($id)
    {
        return new Reference($id);
    }

    protected function processTaggedServices(Definition $definition, $services)
    {
        foreach ($services as $id => $tagAttributes) {
            $definition->addMethodCall($this->methodName,  $this->resolveParameters($id, $tagAttributes));
        }
    }

    protected function resolveParameters($serviceId, $tagAttributes)
    {
        $parameters = array($this->resolveServiceId($serviceId));
        foreach ($this->attributes as $key => $value) {
            if (is_numeric($key)) {
                $name = $value;
                $hasDefault = false;
                $default = null;
            } else {
                $name = $key;
                $hasDefault = true;
                $default = $value;
            }
            try {
                $parameter = $this->getAttribute($tagAttributes, $name, $serviceId);
            } catch (\RuntimeException $exception) {
                if ($hasDefault) {
                    $parameter = $default;
                } else {
                    throw $exception;
                }
            }
            $parameters[] = $parameter;
        }
        return $parameters;
    }
}
