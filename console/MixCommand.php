<?php

namespace JaxWilko\Mix\Console;

use Cms\Classes\Theme;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use File;
use System\Classes\PluginManager;
use Winter\Storm\Exception\ApplicationException;

class MixCommand extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'mix';

    /**
     * @var string The console command description.
     */
    protected $description = 'Generate assets via mix';

    /**
     * @var string The name of mix configs
     */
    protected $configFile = 'winter.mix.js';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        if (!file_exists(base_path() . '/node_modules/webpack/bin/webpack.js')) {
            throw new ApplicationException('webpack bin not found, try running the installer');
        }

        $configs = $this->getMixConfigs();

        $env = [
            'NODE_ENV' => $this->option('production') ? 'production' : 'development'
        ];

        $watch = !!$this->option('watch');

        if ($this->option('theme')) {
            if (!isset($configs['theme'])) {
                throw new ApplicationException('Theme has no ' . $this->configFile);
            }

            return $this->mix($configs['theme'], $env, $watch);
        }

        if ($this->option('plugin')) {
            $plugin = strtolower($this->option('plugin'));
            if (!isset($configs['plugins'][$plugin])) {
                throw new ApplicationException('Plugin not found, it may have no config file');
            }

            return $this->mix($configs['plugins'][$plugin], $env, $watch);
        }

        if ($watch) {
            throw new ApplicationException('You cannot watch multiple compiles');
        }

        if (isset($configs['theme'])) {
            $this->mix($configs['theme'], $env, false, 'theme');
        }

        foreach ($configs['plugins'] as $plugin => $config) {
            $this->mix($config, $env, false, $plugin);
        }

        return 0;
    }

    /**
     * Find all available mix configs
     */
    public function getMixConfigs(): array
    {
        $configs = [
            'plugins' => []
        ];

        $mixConfig = Theme::getActiveTheme()->getPath() . '/' . $this->configFile;

        if (file_exists($mixConfig)) {
            $configs['theme'] = $mixConfig;
        }

        foreach (PluginManager::instance()->getPlugins() as $plugin) {
            $path = $plugin->getPluginPath() . '/' . $this->configFile;
            if (file_exists($path)) {
                $configs['plugins'][strtolower($plugin->getPluginIdentifier())] = $path;
            }
        }

        return $configs;
    }

    public function makeCommand(bool $watch): array
    {
        $command = [
            base_path() . '/node_modules/webpack/bin/webpack.js',
            '--progress',
            '--config=' . $this->getFixturePath()
        ];

        if ($watch) {
            $command[] = '--watch';
        }

        return $command;
    }

    protected function mix(string $config, array $env = [], bool $watch = false, string $name = null): int
    {
        if ($name) {
            $this->info('compiling: ' . $name);
        }

        $this->createConfig($config, $watch);
        $statusCode = $this->executeMixCommand($this->makeCommand($watch), dirname($config), $env, $watch ? null : 120);
        $this->removeConfig();
        return $statusCode;
    }

    protected function executeMixCommand(array $command, string $workingDir, array $env = [], ?int $timeout = 60): int
    {
        $process = new Process($command, $workingDir, $env, null, $timeout);
        try {
            $process->setTty(true);
        } catch (\Throwable $e) {
            // This will fail on unsupported systems
        }
        return $process->run(function ($status, $stdout) {
            if ($this->option('verbose')) {
                $this->getOutput()->write($stdout);
            }
        });
    }

    public function createConfig(string $mixConfigPath, bool $watch): MixCommand
    {
        $notificationInject = $watch ? '' : 'mix._api.disableNotifications();';

        $config = str_replace(
            ['%base%', '%notificationInject%', '%mixConfigPath%'],
            [base_path(), $notificationInject, $mixConfigPath],
            File::get($this->getFixturePath() . '.fixture')
        );

        file_put_contents($this->getFixturePath(), $config);

        return $this;
    }

    public function removeConfig(): MixCommand
    {
        if (file_exists($this->getFixturePath())) {
            unlink($this->getFixturePath());
        }

        return $this;
    }

    public function getFixturePath()
    {
        return __DIR__ . '/../fixtures/webpack.mix.js';
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
        return [
            ['plugin', 'l', InputOption::VALUE_OPTIONAL, 'Target a plugin.', null],
            ['theme', 't', InputOption::VALUE_NONE, 'Target the active theme.'],
            ['development', 'd', InputOption::VALUE_NONE, 'Run a development compile (this is default).'],
            ['production', 'p', InputOption::VALUE_NONE, 'Run a production compile.'],
            ['watch', 'w', InputOption::VALUE_NONE, 'Run and watch a development compile.']
        ];
    }
}
