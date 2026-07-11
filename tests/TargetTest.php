<?php

namespace HexMakina\Hopper\Tests;

use HexMakina\Hopper\Request;
use HexMakina\Hopper\RouterException;
use HexMakina\Hopper\Target;
use PHPUnit\Framework\TestCase;

/**
 * Pins down Target, which splits an AltoRouter "Controller::method" target
 * string into its two halves and rejects anything that is not exactly two
 * ::-separated parts.
 */
class TargetTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
    }

    private function targetFor(string $target): Target
    {
        return new Target(new Request(['target' => $target, 'params' => [], 'name' => 'r']));
    }

    public function testValidTargetSplitsIntoControllerAndMethod(): void
    {
        $target = $this->targetFor('UserController::show');

        $this->assertSame('UserController', $target->controller());
        $this->assertSame('show', $target->method());
    }

    public function testNamespacedControllerIsPreserved(): void
    {
        $target = $this->targetFor('App\\Controllers\\UserController::show');

        $this->assertSame('App\\Controllers\\UserController', $target->controller());
        $this->assertSame('show', $target->method());
    }

    public function testMissingMethodThrows(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('KADRO_ROUTER_ERR_INVALID_TARGET_FORMAT');
        $this->targetFor('UserController');
    }

    public function testTooManySeparatorsThrows(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('KADRO_ROUTER_ERR_INVALID_TARGET_FORMAT');
        $this->targetFor('A::B::C');
    }
}
