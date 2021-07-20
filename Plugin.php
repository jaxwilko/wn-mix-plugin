<?php

namespace JaxWilko\Mix;

use Cms\Classes\Theme;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'          => 'Winter Mix',
            'description'   => 'Provides a Winter CLI for Laravel Mix',
            'author'        => 'Jack Wilkinson',
            'icon'          => 'icon-refresh',
            'homepage'      => 'https://github.com/jaxwilko/wn-mix-plugin'
        ];
    }

    public function register()
    {
        $this->registerConsoleCommand('mix.command', Console\MixCommand::class);
        $this->registerConsoleCommand('mix.install', Console\MixInstall::class);
    }
}
