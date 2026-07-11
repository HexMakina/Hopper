<?php

namespace HexMakina\Hopper\Tests;

use HexMakina\Hopper\Hopper;
use PHPUnit\Framework\TestCase;

/**
 * Pins down hyp(), the reverse router. It generates a URL from a named route
 * (delegating to AltoRouter::generate) and, on any failure, falls back to the
 * home route's URL rather than throwing.
 */
class ReverseRoutingTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
    }

    private function router(string $webBase = ''): Hopper
    {
        return new Hopper('HomeController::index', $webBase, __DIR__);
    }

    public function testHypGeneratesUrlForNamedRoute(): void
    {
        $router = $this->router();
        $router->map(Hopper::REQUEST_GET, '/users', 'UserController::index', 'users');

        $this->assertSame('/users', $router->hyp('users'));
    }

    public function testHypSubstitutesRouteParameters(): void
    {
        $router = $this->router();
        $router->map(Hopper::REQUEST_GET, '/user/[i:id]', 'UserController::show', 'user_show');

        $this->assertSame('/user/42', $router->hyp('user_show', ['id' => 42]));
    }

    public function testHypPrependsBasePath(): void
    {
        $router = $this->router('/app');
        $router->map(Hopper::REQUEST_GET, '/users', 'UserController::index', 'users');

        $this->assertSame('/app/users', $router->hyp('users'));
    }

    public function testHypFallsBackToHomeForUnknownRoute(): void
    {
        $router = $this->router();

        // 'home' route maps to the empty path, so its generated URL is ''
        $this->assertSame('', $router->hyp('does_not_exist'));
    }

    public function testHomeRouteIsRegisteredUnderConstant(): void
    {
        $router = $this->router();

        $this->assertTrue($router->routeExists(Hopper::ROUTE_HOME_NAME));
        $this->assertArrayHasKey(Hopper::ROUTE_HOME_NAME, $router->namedRoutes());
    }
}
