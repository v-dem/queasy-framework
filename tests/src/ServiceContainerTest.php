<?php

namespace queasy\framework\tests;

use PHPUnit\Framework\TestCase;

use queasy\framework\App;
use queasy\container\ContainerException;

class ServiceContainerTest extends TestCase
{
    public function testNoService()
    {
        $container = new App([]);

        $this->assertFalse($container->has('db'));
    }

    public function testNoServiceException()
    {
        $container = new App([]);

        $this->expectException(ContainerException::class);

        $container->get('db');
    }

    public function testService()
    {
        $container = new App([
            'db' => static function($app) {
                return new TestService(123, $app);
            }
        ]);

        $this->assertTrue($container->has('db'));

        $db = $container->get('db');

        $this->assertSame($db, $container->db);

        $this->assertEquals(123, $db->getValue());
        $this->assertSame($container, $db->getContainer());
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

