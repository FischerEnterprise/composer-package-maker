<?php

namespace FischerEnterprise\ComposerPackageMaker\Presets\Plain;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FischerEnterprise\ComposerPackageMaker\Presets\PresetConfig;

class PlainPresetConfig extends PresetConfig
{

    /**
     * Return the preset info (name, description) of the preset
     * @return array [name: string, description: string]
     */
    public function GetPresetInfo(): array
    {
        return [
            'name' => 'Plain',
            'description' => 'A plain package preset without further specifications',
        ];
    }

    /**
     * Get some custom user input if required
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function CustomQuestions(InputInterface $input, OutputInterface $output): void
    {

    }

    /**
     * Create the project structure
     *
     * @param string $targetDirectory
     * @return void
     */
    public function CreatePreset(string $targetDirectory): void
    {
        // Create base folders
        mkdir("$targetDirectory/src", 0777, true);

        // Create default files
        // /.gitignore
        file_put_contents(
            "$targetDirectory/.gitignore",
            $this->GetPresetContent(__DIR__ . '/preset/.gitignore.php')
        );

        // /composer.json
        file_put_contents(
            "$targetDirectory/composer.json",
            $this->GetPresetContent(__DIR__ . '/preset/composer.json.php')
        );

        // /README.md
        file_put_contents(
            "$targetDirectory/README.md",
            $this->GetPresetContent(__DIR__ . '/preset/README.md.php')
        );

        // Create test structure if needed
        if ($this->params['with_tests']) {
            $this->createTestStructure($targetDirectory);
        }
    }

    /**
     * Create the required folderstructure for phpunit tests
     *
     * @param string $targetDirectory
     * @return void
     */
    private function createTestStructure(string $targetDirectory): void
    {
        // Create folders
        mkdir("$targetDirectory/tests/Feature", 0777, true);
        mkdir("$targetDirectory/tests/Unit", 0777, true);

        // Create files
        // /phpunit.xml
        file_put_contents(
            "$targetDirectory/phpunit.xml",
            $this->GetPresetContent(__DIR__ . '/preset/phpunit.xml.php')
        );

        // /tests/TestCase.php
        file_put_contents(
            "$targetDirectory/tests/TestCase.php",
            $this->GetPresetContent(__DIR__ . '/preset/tests/TestCase.php.php')
        );

    }
}
