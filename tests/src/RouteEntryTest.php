<?php

namespace queasy\framework\tests;

use PHPUnit\Framework\TestCase;

use queasy\framework\RouteEntry;

class RouteEntryTest extends TestCase
{
    public function testGetHandler()
    {
        $route = new RouteEntry('testHandler', array());

        $this->assertEquals('testHandler', $route->getHandler());
    }

    public function testGetArguments()
    {
        $route = new RouteEntry('testHandler', array(12, 23));

        $this->assertIsArray($route->getArguments());
        $this->assertEquals(array(12, 23), $route->getArguments());
    }
}

