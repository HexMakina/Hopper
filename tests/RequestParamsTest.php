<?php

namespace HexMakina\Hopper\Tests;

use HexMakina\Hopper\Request;
use PHPUnit\Framework\TestCase;

/**
 * Pins down Request::params()/submitted() as they behave TODAY, including two
 * known defects that Phase 1 step 6 will change:
 *   - query parameters ($_GET) override route (path) parameters of the same name
 *   - values are urldecode()'d again, on top of PHP's own decoding of $_GET
 * These assertions intentionally encode current behavior so the later fix has a
 * red test to flip.
 */
class RequestParamsTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
        $_POST = [];
    }

    private function request(array $routeParams): Request
    {
        return new Request(['target' => 'C::m', 'params' => $routeParams, 'name' => 'r']);
    }

    public function testParamsMergesRouteAndQueryParameters(): void
    {
        $_GET = ['page' => '2'];
        $request = $this->request(['id' => '7']);

        $this->assertSame('7', $request->params('id'));
        $this->assertSame('2', $request->params('page'));
    }

    public function testNamelessParamsReturnsFullMergedArray(): void
    {
        $_GET = ['page' => '2'];
        $request = $this->request(['id' => '7']);

        $this->assertSame(['id' => '7', 'page' => '2'], $request->params());
    }

    /** KNOWN BUG: query param shadows the path param of the same name. */
    public function testQueryParameterOverridesRouteParameter(): void
    {
        $_GET = ['id' => '999'];
        $request = $this->request(['id' => '7']);

        $this->assertSame('999', $request->params('id'));
    }

    public function testUnknownParameterReturnsNull(): void
    {
        $request = $this->request(['id' => '7']);

        $this->assertNull($request->params('missing'));
    }

    /** KNOWN BUG: $_GET is already decoded by PHP; params() decodes it a second time. */
    public function testStringParamsAreUrldecoded(): void
    {
        $_GET = ['q' => 'a%2Bb'];
        $request = $this->request([]);

        $this->assertSame('a+b', $request->params('q'));
    }

    public function testNonStringParamsAreReturnedAsIs(): void
    {
        $request = $this->request(['flags' => ['a', 'b']]);

        $this->assertSame(['a', 'b'], $request->params('flags'));
    }

    public function testSubmittedReadsFromPost(): void
    {
        $_POST = ['name' => 'grace'];
        $request = $this->request([]);

        $this->assertSame('grace', $request->submitted('name'));
        $this->assertSame(['name' => 'grace'], $request->submitted());
        $this->assertNull($request->submitted('absent'));
    }

    public function testNameReturnsRouteName(): void
    {
        $request = $this->request([]);

        $this->assertSame('r', $request->name());
    }
}
