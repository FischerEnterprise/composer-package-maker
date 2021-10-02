<?='<?php'?>


namespace <?=$default_namespace?>Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void {
        parent::tearDown();
    }


    protected function getPackageProviders($app)
    {
        return [
            \<?=$default_namespace?><?=kebab_case_to_pascal_case($package_name)?>ServiceProvider::class,
        ];
    }
}
