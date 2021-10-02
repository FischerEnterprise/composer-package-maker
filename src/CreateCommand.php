<?php

namespace FischerEnterprise\ComposerPackageMaker;

use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{

    /**
     * Configure the command options
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('create')
            ->setDescription('Create a new composer package')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the new package')
            ->addArgument('preset', InputArgument::OPTIONAL, 'The package preset to use', 'plain')
            ->addOption('test', 't', InputOption::VALUE_NONE, 'Creates a test setup for phpunit')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
    }

    /**
     * Execute the command
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Display logo
        $output->write(PHP_EOL . $this->getPrintableLogo() . PHP_EOL . PHP_EOL);

        // Get required parameters
        $name = $input->getArgument('name');
        $directory = $name !== '.' ? getcwd() . '/' . $name : '.';

        // Ask for additional info
        $packageName = $this->ask($input, $output, 'Package name', $name);
        $packageDescription = $this->ask($input, $output, 'Package description', 'A new package');
        $vendorName = $this->ask($input, $output, 'Vendor name');

        $defaultNamespace = kebab_case_to_pascal_case($vendorName) . '\\' . kebab_case_to_pascal_case($packageName) . '\\';
        $defaultNamespace = $this->ask($input, $output, 'Default namespace', $defaultNamespace);

        $authorName = $this->ask($input, $output, 'Author name');
        $authorEmail = $this->ask($input, $output, 'Author email');

        // Check if folder exists
        if (!$input->getOption('force')) {
            $this->verifyPackageDoesNotExist($directory);
        }

        // Check illegal usage of force option
        if ($input->getOption('force') && $directory === '.') {
            throw new RuntimeException('Cannot use --force option when using current directory for installation!');
        }

        // Clear directory if needed
        if ($directory != '.' && $input->getOption('force')) {
            $clearCommand = '';
            if (PHP_OS_FAMILY == 'Windows') {
                $clearCommand = "rd /s /q \"$directory\"";
            } else {
                $clearCommand = "rm -rf \"$directory\"";
            }

            if (!$this->runCommand($clearCommand, $input, $output)) {
                throw new RuntimeException('Directory clear command was not successfull');
            }
        }

        // Get config class for preset
        $PresetConfigClass = (require __DIR__ . '/../presets/presets.php')[$input->getArgument('preset')] ?? null;

        if ($PresetConfigClass === null) {
            throw new RuntimeException('Preset not supported');
        }

        $presetConfig = new $PresetConfigClass(
            $vendorName, $packageName, $packageDescription,
            $defaultNamespace, $authorName, $authorEmail, $input->getOption('test')
        );

        // Run preset
        $presetConfig->CustomQuestions($input, $output);
        $presetConfig->CreatePreset($directory);

        return 0;
    }

    /**
     * Ask the user a question
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $question
     * @param string $default
     * @return string
     */
    protected function ask(InputInterface $input, OutputInterface $output, string $question, string $default = null)
    {
        $questionText = $question;
        if ($default !== null) {
            $questionText .= " [$default]";
        }
        $questionText .= ': ';

        $questionHelper = $this->getHelper('question');
        return $questionHelper->ask($input, $output, new Question($questionText, $default));
    }

    #region Get Printable Logo

    /**
     * Get the logo to display in console
     *
     * @return string
     */
    protected function getPrintableLogo(): string
    {
        $logo = '<fg=red>
_________
\_   ___ \  ____   _____ ______   ____  ______ ___________
/    \  \/ /  _ \ /     \\\\____ \ /  _ \/  ___// __ \_  __ \
\     \___(  <_> )  Y Y  \  |_> >  <_> )___ \\\\  ___/|  | \/
 \______  /\____/|__|_|  /   __/ \____/____  >\___  >__|
        \/             \/|__|              \/     \/
__________                __
\______   \_____    ____ |  | _______     ____   ____
 |     ___/\__  \ _/ ___\|  |/ /\__  \   / ___\_/ __ \
 |    |     / __ \\\\  \___|    <  / __ \_/ /_/  >  ___/
 |____|    (____  /\___  >__|_ \(____  /\___  / \___  >
                \/     \/     \/     \//_____/      \/
   _____          __
  /     \ _____  |  | __ ___________
 /  \ /  \\\\__  \ |  |/ // __ \_  __ \
/    Y    \/ __ \|    <\  ___/|  | \/
\____|__  (____  /__|_ \\\\___  >__|
        \/     \/     \/    \/
</>';
        return $logo;
    }

    #endregion Get Printable Logo

    /**
     * Verify that the package does not already exist.
     *
     * @param string $directory
     * @return void
     */
    protected function verifyPackageDoesNotExist($directory)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory !== getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        $composerPath = getcwd() . '/composer.phar';

        if (file_exists($composerPath)) {
            return '"' . PHP_BINARY . '" ' . $composerPath;
        }

        return 'composer';
    }

    /**
     * Create a new file based on a preset
     *
     * @param string $target
     * @param string $preset
     * @param array $values
     * @param array $cases
     * @return void
     */
    protected function createFromPreset($target, $preset, $values = [], $cases = [])
    {
        $content = file(preset_dir($preset));
        $filteredContent = [];

        foreach ($cases as $key => $enabled) {
            $print = true;
            foreach ($content as $line) {
                if (\str_contains($line, "$[IF[$key]]")) {
                    $print = $enabled;
                } else if (\str_contains($line, "$[ENDIF[$key]]")) {
                    $print = true;
                } else if ($print) {
                    array_push($filteredContent, $line);
                }
            }
        }

        if (count($cases) === 0) {
            $filteredContent = $content;
        }

        $content = implode('', $filteredContent);

        foreach ($values as $key => $value) {
            $content = str_replace("$[[$key]]", $value, $content);
        }
        file_put_contents($target, $content);
    }

    /**
     * Run the given command.
     *
     * @param  string                                            $command
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return Process
     */
    protected function runCommand($command, InputInterface $input, OutputInterface $output)
    {
        $process = Process::fromShellCommandline($command, null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $output->writeln('Warning: ' . $e->getMessage());
            }
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write('    ' . $line);
        });

        return $process;
    }

}
