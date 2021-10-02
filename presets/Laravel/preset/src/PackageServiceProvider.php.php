<?='<?php'?>


namespace <?=substr($default_namespace, 0, -1)?>;

class <?=kebab_case_to_pascal_case($package_name)?>ServiceProvider extends Illuminate\Support\ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {

    }
}
