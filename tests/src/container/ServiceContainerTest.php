<?php

namespace queasy\framework\container\tests;

use PHPUnit\Framework\TestCase;

use queasy\framework\container\ServiceContainer;
use queasy\framework\container\ContainerException;

class ServiceContainerTest extends TestCase
{
    public function testNoService()
    {
        $container = new ServiceContainer([]);

        $this->assertFalse($container->has('app'));
    }

    public function testNoServiceException()
    {
        $container = new ServiceContainer([]);

        $this->expectException(ContainerException::class);

        $service = $container->get('app');
    }

    public function testService()
    {
        $container = new ServiceContainer([
            'app' => [
                'class' => TestService::class,
                'construct' => [
                    [
                        'value' => 123
                    ], [
                        'service' => 'this'
                    ]
                ]
            ]
        ]);

        $this->assertTrue($container->has('app'));

        $app = $container->get('app');

        $this->assertSame($app, $container->app);

        $this->assertEquals(123, $app->getValue());
        $this->assertSame($container, $app->getContainer());
    }
}

/**
 * @codeCoverageIgnore
 */
class TestService
{
    private $value;

    private $container;
    
    public function __construct($value, $container)
    {
        $this->value = $value;
        $this->container = $container;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getContainer()
    {
        return $this->container;
    }
}

