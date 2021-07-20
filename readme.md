# Winter Mix

This plugin is still under active development and should not be used in production without
serious testing.

### Installation

We leverage the npm v7 feature workspaces to manage plugin resources from the application's root.

To set this up, you need to add the following to your root `package.json`:

```json
"workspaces": {
    "packages": [
        "plugins/*/*"
    ]
}
```

Then run `npm i`. This can be done for you via the `mix:install` command.

### Usage

Once installed, create a file at the root of a plugin or theme with the name `winter.mix.js`.

This file will be 90% the same as any laravel mix config file. However, you must set mix's `publicPath`.

Which you can do simply as:

```javascript
mix.setPublicPath(__dirname);
// or
mix.setPublicPath(__dirname + '/assets');
```

For example if your plugin file structure was:

```
plugins/acme/myPlugin
  - assets
  - src
    - app.scss
  - Plugin.php
```

Then you could use the following:

```javascript
let mix = require('laravel-mix');

mix.setPublicPath(__dirname + '/assets');

mix.sass('src/app.scss', 'app.css');
```

### Compiling

Once you're all set up, you can use the `mix` command to run compilation.

```
Options:
  -l, --plugin[=PLUGIN]  Target a plugin.
  -t, --theme            Target the theme
  -d, --development      Run a development compile (this is default).
  -p, --production       Run a production compile.
  -w, --watch            Run and watch a development compile.
  -v                     Extra output
```

By default, the `mix` command runs in development mode, you can change this by using the `--production` flag.

You can only watch one thing at a time, so you must specify either `--theme` or `--plugin author.plugin`.
