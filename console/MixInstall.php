<?php

namespace JaxWilko\Mix\Console;

use Illuminate\Console\Command;
use File;
use Symfony\Component\Process\Process;

class MixInstall extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'mix:install';

    /**
     * @var string The console command description.
     */
    protected $description = 'Install mix plugin';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        if (!version_compare($this->getNpmVersion(), '7', '>')) {
            $this->error('npm not found or invalid version');
            $this->warn('Either npm is not installer or npm is not > 7.0. Please install or update npm to continue');
            return 1;
        }

        if ((file_exists(base_path('package.json')) ? $this->updatePackage() : $this->createPackage()) > 0) {
            return 1;
        }

        if ($this->confirm('Run `npm install` now?', true)) {
            $this->npmInstall();
        }

        return 0;
    }

    protected function updatePackage(): int
    {
        $package = json_decode(File::get(base_path('package.json')));

        if (isset($package->workspaces)) {
            $this->error('Found package.json in base path and contains workspaces');
            $this->warn(
                'You have a pre-existing package.json with workspaces set up. ' .
                'Please read the readme for more info.'
            );

            return 1;
        }

        $package->workspaces = [
            'packages' => [
                'plugins/*/*'
            ]
        ];

        if (!$this->confirm('Continuing will amend your package.json, are you sure?')) {
            return 1;
        }

        File::put(base_path('package.json'), json_encode($package, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

        $this->info('package.json updated');

        return 0;
    }

    protected function createPackage(): int
    {
        $config = str_replace(
            '%appName%',
            config('app.name'),
            File::get(__DIR__ . '/../fixtures/package.json.fixture')
        );

        File::put(base_path('package.json'), $config);

        $this->info('package.json created');

        return 0;
    }

    protected function npmInstall(): int
    {
        $process = new Process(['npm', 'install'], base_path());
        $process->run(function ($status, $stdout) {
            $this->getOutput()->write($stdout);
        });

        return $process->getExitCode();
    }

    protected function getNpmVersion()
    {
        $process = new Process(['npm', '--version']);
        $process->run();
        return $process->getOutput();
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
