<?php

namespace Adtention\DatepickerField\Tests;

use Adtention\DatepickerField\FieldServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FieldServiceProvider::class,
        ];
    }
}
