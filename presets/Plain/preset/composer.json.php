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
    }<?=$with_tests ? ',' : ''?>

<?php if ($with_tests): ?>
    "autoload_dev": {
        "psr-4": {
            "<?=str_replace('\\', '\\\\', $default_namespace)?>Tests\\": "tests"
        }
    },
    "require_dev": {
        "phpunit/phpunit": "^9.0"
    },
    "scripts": {
        "test": "sh vendor/bin/phpunit",
        "test-f": "sh vendor/bin/phpunit --filter"
    }
<?php endif?>
}