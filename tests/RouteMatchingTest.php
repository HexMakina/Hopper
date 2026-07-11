<?php

namespace HexMakina\Hopper\Tests;

use HexMakina\Hopper\Hopper;
use HexMakina\Hopper\RouterException;
use PHPUnit\Framework\TestCase;

/**
 * Pins down Hopper::match() as it behaves today: on a hit it returns the
 * [controller, method] pair parsed from the AltoRouter target; on a miss it
 * throws RouterException instead of returning AltoRouter's false.
 */
class RouteMatchingTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
        $_POST = [];
    }

    private function router(): Hopper
    {
        // empty web base keeps AltoRouter's base-path stripping out of the way
        return new Hopper('HomeController::index', '', __DIR__);
    }

    public function testMatchReturnsControllerAndMethodPair(): void
    {
        $router = $this->router();
        $router->map(Hopper::REQUEST_GET, '/users', 'UserController::index', 'users');

        $this->assertSame(['UserController', 'index'], $router->match('/users', 'GET'));
    }

    public function testMatchReturnsHomeTargetForEmptyPath(): void
    {
        $router = $this->router();

        $this->assertSame(['HomeController', 'index'], $router->match('', 'GET'));
    }

    public function testMatchExtractsPathParametersIntoRequest(): void
    {
        $router = $this->router();
        $router->map(Hopper::REQUEST_GET, '/user/[i:id]', 'UserController::show', 'user_show');

        $this->assertSame(['UserController', 'show'], $router->match('/user/42', 'GET'));
        $this->assertSame('42', $router->request()->params('id'));
    }

    public function testUnmatchedRouteThrowsRouterException(): void
    {
        $router = $this->router();

        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('KADRO_ROUTER_ERR_ROUTE_MATCH_FALSE');
        $router->match('/nope', 'GET');
    }

    public function testWrongMethodDoesNotMatch(): void
    {
        $router = $this->router();
        $router->map(Hopper::REQUEST_POST, '/users', 'UserController::store', 'users_store');

        $this->expectException(RouterException::class);
        $router->match('/users', 'GET');
    }
}
