<?php

namespace Sammy1992\Haina\Tests;

use Mockery;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        Mockery::globalHelpers();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
