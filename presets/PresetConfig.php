<?php

namespace FischerEnterprise\ComposerPackageMaker\Presets;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Manage a package preset
 * @author Ben Fischer
 */
abstract class PresetConfig
{
    /**
     * Stores package specific parameters
     * @var array
     */
    protected $params;

    /**
     * Constructor stores default parameters in $params
     */
    public function __construct($vendorName, $packageName, $packageDescription, $defaultNamespace, $authorName, $authorEmail, $withTests)
    {
        $this->params = [
            'vendor_name' => $vendorName,
            'package_name' => $packageName,
            'package_description' => $packageDescription,
            'default_namespace' => $defaultNamespace,
            'author_name' => $authorName,
            'author_email' => $authorEmail,
            'with_tests' => $withTests,
        ];
    }

    /**
     * Return the preset info (name, description) of the preset
     * @return array [name: string, description: string]
     */
    abstract public function GetPresetInfo(): array;

    /**
     * Get some custom user input if required
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    abstract public function CustomQuestions(InputInterface $input, OutputInterface $output): void;

    /**
     * Create the project structure
     *
     * @param string $targetDirectory
     * @return void
     */
    abstract public function CreatePreset(string $targetDirectory): void;

    /**
     * Get the content of a preset file
     *
     * @param string $presetFile Path to the preset file
     * @return string
     */
    protected function GetPresetContent($presetFile): string
    {
        foreach ($this->params as $key => $value) {
            $$key = $value;
        }

        ob_start();
        include $presetFile;
        $content = ob_get_clean();

        foreach ($this->params as $key => $value) {
            unset($$key);
        }

        return $content;
    }
}
