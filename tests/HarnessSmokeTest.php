<?php

namespace HexMakina\Hopper\Tests;

use HexMakina\Hopper\Hopper;
use HexMakina\Hopper\Request;
use HexMakina\Hopper\RouterException;
use HexMakina\Hopper\Target;
use PHPUnit\Framework\TestCase;

/**
 * Proves the harness wiring: autoloading of src/ classes, the dev
 * dependencies, and superglobal setup/teardown for later tests.
 */
class HarnessSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function testSourceClassesAutoload(): void
    {
        $this->assertTrue(class_exists(Hopper::class));
        $this->assertTrue(class_exists(Request::class));
        $this->assertTrue(class_exists(Target::class));
        $this->assertTrue(class_exists(RouterException::class));
    }

    public function testHopperConstructsAndMapsHomeRoute(): void
    {
        $router = new Hopper('HomeController::index', '/app', __DIR__);

        $this->assertTrue($router->routeExists(Hopper::ROUTE_HOME_NAME));
        $this->assertSame('/app', $router->basePath());
    }
}
