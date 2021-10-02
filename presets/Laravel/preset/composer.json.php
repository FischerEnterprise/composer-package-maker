{
    "name": "<?=$vendor_name?>/<?=$package_name?>",
    "description": "<?=$package_description?>",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "<?=$author_name?>",
            "email": "<?=$author_email?>"
        }
    ],
    "require": {},
    "autoload": {
        "psr-4": {
            "<?=str_replace('\\', '\\\\', $default_namespace)?>": "src"
        }
    },
<?php if ($with_tests): ?>
    "autoload_dev": {
        "psr-4": {
            "<?=str_replace('\\', '\\\\', $default_namespace)?>Tests\\": "tests",
            "<?=str_replace('\\', '\\\\', $default_namespace)?>Database\\Factories\\": "database/factories"
        }
    },
    "require_dev": {
        "phpunit/phpunit": "^9.0",
        "orchestra/testbench": "^6.0"
    },
    "scripts": {
        "fischer-enterprise/laravel-package-commands": "dev-master",
        "test": "sh vendor/bin/phpunit",
        "test-f": "sh vendor/bin/phpunit --filter"
    },
<?php else: ?>
    "require_dev": {
        "fischer-enterprise/laravel-package-commands": "dev-master"
    }
<?php endif?>
    "extra": {
        "laravel": {
            "providers": [
                "<?=str_replace('\\', '\\\\', $default_namespace)?><?=kebab_case_to_pascal_case($package_name)?>ServiceProvider"
            ]
        }
    }
}