<?php

namespace Maba\Component\DependencyInjection\Tests;

use Maba\Component\DependencyInjection\AddTaggedByPriorityCompilerPass;

class AddTaggedByPriorityCompilerPassTest extends AddTaggedCompilerPassTest
{

    protected function createCompilerPass($parentServiceId, $tagName, $methodName, array $attributes)
    {
        return new AddTaggedByPriorityCompilerPass($parentServiceId, $tagName, $methodName, $attributes);
    }

    public function dataProvider()
    {
        return array_merge(parent::dataProvider(), array(
            'leaves original order if adheres priority' => array(
                array(
                    array($this->buildReturn('provider_id')),
                    array($this->buildReturn('provider_id_2')),
                ),
                true,
                array(
                    'provider_id' => array(array('priority' => 0)),
                    'provider_id_2' => array(array('priority' => 1)),
                ),
            ),
            'orders by priority' => array(
                array(
                    array($this->buildReturn('provider_id')),
                    array($this->buildReturn('provider_id_2')),
                ),
                true,
                array(
                    'provider_id_2' => array(array('priority' => 1)),
                    'provider_id' => array(array('priority' => 0)),
                ),
            ),
            'priority default to 0' => array(
                array(
                    array($this->buildReturn('provider_id')),
                    array($this->buildReturn('provider_id_2')),
                    array($this->buildReturn('provider_id_3')),
                ),
                true,
                array(
                    'provider_id_2' => array(array()),
                    'provider_id' => array(array('priority' => -10)),
                    'provider_id_3' => array(array('priority' => 9000)),
                ),
            ),
            'passes arguments when priority set' => array(
                array(
                    array($this->buildReturn('provider_id'), 'default'),
                    array($this->buildReturn('provider_id_2'), '2'),
                    array($this->buildReturn('provider_id_3'), '3'),
                ),
                true,
                array(
                    'provider_id_2' => array(array('name' => '2')),
                    'provider_id' => array(array('priority' => -10)),
                    'provider_id_3' => array(array('priority' => 9000, 'name' => '3')),
                ),
                array('name' => 'default'),
            ),
            'can pass priority as an argument' => array(
                array(
                    array($this->buildReturn('provider_id'), 'default', -10),
                    array($this->buildReturn('provider_id_2'), '2', 'default priority'),
                    array($this->buildReturn('provider_id_3'), '3', 9000),
                ),
                true,
                array(
                    'provider_id_2' => array(array('name' => '2')),
                    'provider_id' => array(array('priority' => -10)),
                    'provider_id_3' => array(array('priority' => 9000, 'name' => '3')),
                ),
                array('name' => 'default', 'priority' => 'default priority'),
            ),
        ));
    }
}
