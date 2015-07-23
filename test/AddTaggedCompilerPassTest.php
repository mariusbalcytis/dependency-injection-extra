<?php

namespace Maba\Component\DependencyInjection\Tests;

use Maba\Component\DependencyInjection\AddTaggedCompilerPass;
use Symfony\Component\DependencyInjection\Reference;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_Builder_InvocationMocker as InvocationMocker;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddTaggedCompilerPassTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param array $expected
     * @param boolean $hasDefinition
     * @param array $tagged
     * @param array $attributes
     *
     * @dataProvider dataProvider
     */
    public function testProcess($expected, $hasDefinition, $tagged, array $attributes = array())
    {
        $definition = new Definition();

        /** @var MockObject|InvocationMocker|ContainerBuilder $container */
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'hasDefinition',
                'getDefinition',
                'findTaggedServiceIds',
            ))
            ->getMock()
        ;
        $container
            ->expects($this->once())
            ->method('hasDefinition')
            ->with('registry')
            ->will($this->returnValue($hasDefinition))
        ;
        $container
            ->method('getDefinition')
            ->with('registry')
            ->will($this->returnValue($definition))
        ;
        $container
            ->method('findTaggedServiceIds')
            ->with('provider')
            ->will($this->returnValue($tagged))
        ;

        $compilerPass = $this->createCompilerPass('registry', 'provider', 'addProvider', $attributes);

        if ($expected === false) {
            $this->setExpectedException('RuntimeException');
            $compilerPass->process($container);

        } else {
            $compilerPass->process($container);
            $this->assertCount(count($expected), $definition->getMethodCalls());
            foreach ($definition->getMethodCalls() as $key => $methodCall) {
                $this->assertSame('addProvider', $methodCall[0]);
                $this->assertEquals($expected[$key], $methodCall[1]);
            }
        }
    }

    protected function createCompilerPass($parentServiceId, $tagName, $methodName, array $attributes)
    {
        return new AddTaggedCompilerPass($parentServiceId, $tagName, $methodName, $attributes);
    }

    public function dataProvider()
    {
        return array(
            'does not fail if no parent definition' => array(
                array(),
                false,
                array('provider_id' => array(array('name' => 'value'))),
            ),
            'does not fail if no tags' => array(
                array(),
                true,
                array(),
            ),
            'adds single provider' => array(
                array(
                    array($this->buildReturn('provider_id')),
                ),
                true,
                array(
                    'provider_id' => array(array()),
                ),
            ),
            'adds several providers' => array(
                array(
                    array($this->buildReturn('provider_id')),
                    array($this->buildReturn('provider_id_2')),
                ),
                true,
                array(
                    'provider_id' => array(array()),
                    'provider_id_2' => array(array()),
                ),
            ),
            'ignores additional attributes' => array(
                array(
                    array($this->buildReturn('provider_id')),
                    array($this->buildReturn('provider_id_2')),
                ),
                true,
                array(
                    'provider_id' => array(array(), array('a' => 'b')),
                    'provider_id_2' => array(array('name' => 'value')),
                ),
            ),
            'adds provided attribute' => array(
                array(
                    array($this->buildReturn('provider_id'), 'a'),
                    array($this->buildReturn('provider_id_2'), 'b'),
                ),
                true,
                array(
                    'provider_id' => array(array(), array('name' => 'a')),
                    'provider_id_2' => array(array('name' => 'b')),
                ),
                array('name'),
            ),
            'adds several attributes' => array(
                array(
                    array($this->buildReturn('provider_id'), 'a', 'a1'),
                    array($this->buildReturn('provider_id_2'), 'b', 'b1'),
                ),
                true,
                array(
                    'provider_id' => array(array('name2' => 'a1'), array('name' => 'a')),
                    'provider_id_2' => array(array('name2' => 'b1', 'name' => 'b')),
                ),
                array('name', 'name2'),
            ),
            'adds optional attributes' => array(
                array(
                    array($this->buildReturn('provider_id'), 'a', 'a1', 'default', 'a2'),
                    array($this->buildReturn('provider_id_2'), 'b', 'b1', 'b3', 'b2'),
                ),
                true,
                array(
                    'provider_id' => array(array('name2' => 'a1', 'name3' => 'a2'), array('name' => 'a')),
                    'provider_id_2' => array(array('name2' => 'b1', 'name' => 'b', 'name3' => 'b2', 'optional' => 'b3')),
                ),
                array('name', 'name2', 'optional' => 'default', 'name3'),
            ),
            'fails if no required attribute provided' => array(
                false,  // false - assert that exception is thrown
                true,
                array(
                    'provider_id' => array(array('name2' => 'a1', 'name3' => 'a2')),
                    'provider_id_2' => array(array('name2' => 'b1', 'optional' => 'b3')),
                ),
                array('name2', 'optional' => 'default', 'name3'),
            ),
        );
    }

    protected function buildReturn($serviceId)
    {
        return new Reference($serviceId);
    }
}
