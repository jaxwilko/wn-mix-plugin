# Winter Mix

### Installation

> Notice: NPN >=`7.*` is require for this plugin

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

Then you could use the following in your winter.mix.js file:

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
  -t, --theme            Target the active theme.
  -d, --development      Run a development compile (this is default).
  -p, --production       Run a production compile.
  -w, --watch            Run and watch a development compile.
  -v                     Extra output
```

By default, the `mix` command runs in development mode, you can change this by using the `--production` flag.

You can only watch one thing at a time, so you must specify either `--theme` or `--plugin author.plugin`.

### Examples

Compile theme assets:
- `./artisan mix --theme`
- `./artisan mix --theme --production` (for production)
- `./artisan mix --theme --watch` (in watch mode)

Compile plugin assets:
- `./artisan mix --plugin jaxwilko.mix`
- `./artisan mix --plugin jaxwilko.mix --production` (for production)
- `./artisan mix --plugin jaxwilko.mix --watch` (in watch mode)

Compile everything:
- `./artisan mix`
- `./artisan mix --production` (for production)
