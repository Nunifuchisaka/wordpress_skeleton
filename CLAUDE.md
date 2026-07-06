# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repo is

A skeleton for developing a single WordPress theme (and, eventually, a companion plugin) with a modern build pipeline, run locally via Docker. There is no PHP framework or business logic beyond the theme templates — the real work is authoring SCSS/JS/EJS under `src/theme/` and wiring the webpack pipeline that turns it into the theme WordPress actually loads.

## Commands

- `npm install` — install build dependencies (no `package-lock.json` is committed; see `.gitignore`).
- `npm start` (= `webpack`) — runs both webpack configs (see Architecture) in watch mode. This is the normal "dev loop" command; leave it running while editing `src/`.
- `docker compose up` — starts WordPress (official `wordpress:latest` image) on `http://localhost:8081` plus a MariaDB container. Run this alongside `npm start`.
- There are no test or lint npm scripts; Stylelint and ESLint run automatically as webpack plugins during the build (see below) rather than via standalone commands.

## Architecture

### Build pipeline (`webpack.config.js`)

Two webpack configs are exported as an array and always run together (`module.exports = [development, production]`):

1. **`development`** (name: `development`) — compiles `src/theme/css/**/*.scss` (skipping `_partial.scss` files) into unminified CSS in `dist_uncompressed/`. Runs in watch mode. Stylelint runs here via `stylelint-webpack-plugin`. `output.pathinfo: false` is set specifically to stop `mini-css-extract-plugin` from prepending a module-path comment banner to each generated CSS file.
2. **`production`** (name: `production`, `dependencies: ['development']`) — this dependency ordering is load-bearing: webpack waits for `development` to finish before running `production`, because production's `CopyPlugin` step copies `dist_uncompressed/` → `dist/`. Production also:
   - Compiles `src/theme/js/**/*.js` via Babel + Terser directly into `dist/` (ESLint runs here via `eslint-webpack-plugin`).
   - Compiles `src/theme/**/*.ejs` → `.php` via `html-webpack-plugin` + `ejs-plain-loader` (`inject: false`, so templates are plain PHP files, not HTML pages with injected assets).
   - Converts images under `img2webp/` to `.webp` via `sharp` and copies them in.
   - Copies `dist_uncompressed/` → `dist/`, minifying HTML/PHP output via `html-minifier-terser` and CSS via `cssnano` in the process.
   - Runs `browser-sync-webpack-plugin`, watching `dist/**/*.{php,css,js}` for live reload against the site at `localhost:8081`.

So `dist_uncompressed/` is a build-internal intermediate, not something to hand-edit; `dist/` is the real theme output. Both directories are committed to git (this is a skeleton meant to ship a working build).

### EJS → PHP templating

`src/theme/*.ejs` files map 1:1 to theme root PHP files (`header.ejs` → `header.php`, etc.). `functions.ejs` is a thin shell that stitches together `functions/_*.ejs` partials via `<%- include(...) %>` — when changing theme setup behavior (enqueueing assets, `pre_get_posts`, pagination, feeds, etc.), find the relevant `_*.ejs` partial under `src/theme/functions/` rather than editing one monolithic file. Filenames prefixed with `_` (both `.ejs` and `.scss`) are partials/includes and are excluded from webpack's entry globs — only non-`_` files become build outputs.

### Docker / WordPress wiring (`docker-compose.yml`)

- `./htdocs` bind-mounts to `/var/www/html` — this is WordPress core itself (downloaded by the official image's entrypoint on first boot). It's gitignored except for a couple of placeholder files.
- `./dist/theme` and `./dist/plugin` bind-mount onto `wp-content/themes/hoge` and `wp-content/plugins/hoge` respectively — this is how the locally-built theme/plugin become the active theme/plugin without copying files into `htdocs/`.
- Five additional named volumes (`wp_default_theme_2025/2024/2023`, `wp_default_plugin_akismet/hello`) are mounted onto WordPress's bundled default themes/plugins paths specifically to keep them from ever being extracted: the official image's entrypoint skips extracting a bundled theme/plugin if something already occupies that destination path, so mounting an empty volume there is enough to suppress it. Follow this same pattern if a future WordPress version ships a new default theme (e.g. `twentytwentysix`) that needs suppressing too.
- `./php.ini` is intended to bind-mount a file to `/usr/local/etc/php/conf.d/uploads.ini` for upload size limits — note that Docker will silently create `php.ini` as a *directory* instead of failing if the file doesn't exist on the host when the container first starts, silently breaking this mount. If upload-size PHP settings don't seem to apply, check that `./php.ini` on disk is an actual file, not an empty directory.

### `.gitignore` structure for `htdocs/`

The ignore rules for `htdocs/wp-content/{themes,plugins,uploads}` follow a repeating "re-include the directory, then re-exclude its contents, then re-include one placeholder" pattern (needed because a `!`-negated directory does not implicitly un-ignore its own contents in git). If you add a new kept file under one of these directories, add the exclusion inside that directory's own block rather than at the top level — a bare `!/htdocs/wp-content/themes/foo` added elsewhere won't work if a broader `/htdocs/wp-content/themes/*` rule earlier in the file already re-ignored everything.
