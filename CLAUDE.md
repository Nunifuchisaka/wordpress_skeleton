# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repo is

A skeleton for developing a single WordPress theme and companion plugins with a modern build pipeline, run locally via Docker. There is no PHP framework or business logic beyond the theme templates and plugins — the real work is authoring SCSS/JS/EJS under `src/` and wiring the webpack pipeline that turns it into the theme/plugins WordPress actually loads.

## Commands

- `npm install` — install build dependencies (no `package-lock.json` is committed; see `.gitignore`).
- `npm start` (= `webpack`) — runs both webpack configs (see Architecture) in watch mode. This is the normal "dev loop" command; leave it running while editing `src/`.
- `docker compose up` — starts WordPress (official `wordpress:latest` image) on `http://localhost:8081` plus a MariaDB container. Run this alongside `npm start`.
- `npm run db:export` — dumps the WordPress database (posts, settings, etc.) to `db/wordpress_db.sql` so site content can be committed. DB data itself lives in the gitignored `db_data` volume, so run this after changing content you want to keep in the repo.
- `npm run db:import` — restores the database from the committed `db/wordpress_db.sql` (drops and recreates all tables). `db/*.sql` is marked `-text` in `.gitattributes` so line-ending normalization can't corrupt serialized PHP data inside the dump.
- There are no test or lint npm scripts; Stylelint and ESLint run automatically as webpack plugins during the build (see below) rather than via standalone commands. The NCF plugin has its own test runner (see Testing below).

## Architecture

### Build pipeline (`webpack.config.js`)

Two webpack configs are exported as an array and always run together (`module.exports = [development, production]`):

1. **`development`** (name: `development`) — compiles `src/**/*.scss` (skipping `_partial.scss` files) into unminified CSS in `dist_uncompressed/`. Runs in watch mode. Stylelint runs here via `stylelint-webpack-plugin`. `output.pathinfo: false` is set specifically to stop `mini-css-extract-plugin` from prepending a module-path comment banner to each generated CSS file.
2. **`production`** (name: `production`, `dependencies: ['development']`) — this dependency ordering is load-bearing: webpack waits for `development` to finish before running `production`, because production's `CopyPlugin` step copies `dist_uncompressed/` → `dist/`. Production also:
   - Compiles `src/**/*.js` via Babel + Terser directly into `dist/` (ESLint runs here via `eslint-webpack-plugin`).
   - Compiles `src/**/*.ejs` → `.php` via `html-webpack-plugin` + `ejs-plain-loader` (`inject: false`, so templates are plain PHP files, not HTML pages with injected assets).
   - Converts images under `img2webp/` to `.webp` via `sharp` and copies them in.
   - Copies `dist_uncompressed/` → `dist/`, minifying HTML/PHP output via `html-minifier-terser` and CSS via `cssnano` in the process.
   - Runs `browser-sync-webpack-plugin`, watching `dist/**/*.{php,css,js}` for live reload against the site at `localhost:8081`.

So `dist_uncompressed/` is a build-internal intermediate, not something to hand-edit; `dist/` is the real theme/plugin output. Both directories are committed to git (this is a skeleton meant to ship a working build).

Directories under `src/plugins/` are auto-discovered (the `PLUGIN_DIRS` glob), so a new plugin just needs a new directory — its SCSS/JS/EJS get built to `dist/plugins/<name>/` with no webpack config changes. It **does** need a new bind-mount line in `docker-compose.yml` (see below) to appear inside WordPress.

### EJS → PHP templating

`src/theme/*.ejs` files map 1:1 to theme root PHP files (`header.ejs` → `header.php`, etc.). `functions.ejs` is a thin shell that stitches together `functions/_*.ejs` partials via `<%- include(...) %>` — when changing theme setup behavior (enqueueing assets, `pre_get_posts`, pagination, feeds, etc.), find the relevant `_*.ejs` partial under `src/theme/functions/` rather than editing one monolithic file. Filenames prefixed with `_` (both `.ejs` and `.scss`) are partials/includes and are excluded from webpack's entry globs — only non-`_` files become build outputs.

### Plugins (`src/plugins/`)

- **`nunifuchisaka-custom-fields`** (NCF) — a lightweight custom-fields (meta box) plugin. Field definitions live in the *theme*, not the plugin: the theme partial `src/theme/functions/_ncf.ejs` registers them via the `ncf_register_fields` filter. Field types, hooks (`ncf_show_output_code`, `ncf_after_save`), and usage are documented in its `README.md`. Meta keys are prefixed `ncf_`.
- **`ncf-test`** — an in-WordPress test runner for NCF (see Testing).
- **`hoge`** — a minimal sample plugin.
- **`demo-styles`** — dev-only plugin that styles the demo templates (article list, NCF output section) so the theme's own CSS stays clean. Enqueues its `css/demo.css` on `wp_enqueue_scripts` at priority 110 (after the theme's 100). Deactivate or delete the directory in real projects.

### Testing (NCF Test plugin)

`src/plugins/ncf-test/` tests NCF inside the real WordPress environment instead of via PHPUnit. Activate both plugins, then run from admin at ツール > NCF Test — it creates draft fixture posts, replays edit-screen save requests (forged `$_POST` + nonce) against NCF's public methods to verify save/sanitize/render/hook behavior, and deletes the fixtures afterward. While running it temporarily detaches theme-registered `ncf_register_fields` / `ncf_after_save` / `ncf_show_output_code` hooks so results are deterministic.

To add a test: add a `test_xxx()` method to the `Runner` class in `ncf-test.ejs` and call it from `run()` (helpers: `make_post`, `simulate_save`, `render`, `assert_*` — see its `README.md`). The runner duplicates NCF's nonce name as class constants; if it changes on the NCF side, update them together.

Tests can also run headless via a bootstrap script executed with `docker exec wordpress_skeleton php ...` (prefix `MSYS_NO_PATHCONV=1` in Git Bash): require `wp-load.php`, `wp-admin/includes/template.php`, `wp-admin/includes/class-wp-screen.php` and `screen.php`, call `set_current_screen( 'post' )`, set an administrator via `wp_set_current_user`, then instantiate `\NCF_Test\Runner` and print `run()`'s results. Without the WP_Screen includes, `add_meta_box()` resolves screens to `_invalid` and the registration tests fail spuriously.

### Docker / WordPress wiring (`docker-compose.yml`)

- `./htdocs` bind-mounts to `/var/www/html` — this is WordPress core itself (downloaded by the official image's entrypoint on first boot). It's gitignored except for a couple of placeholder files.
- `./dist/theme` bind-mounts onto `wp-content/themes/hoge`, and each `./dist/plugins/<name>` bind-mounts onto `wp-content/plugins/<name>` — this is how the locally-built theme/plugins become available without copying files into `htdocs/`. When adding a plugin under `src/plugins/`, add a matching mount line here and recreate the container (`docker compose up -d wordpress`).
- Five additional named volumes (`wp_default_theme_2025/2024/2023`, `wp_default_plugin_akismet/hello`) are mounted onto WordPress's bundled default themes/plugins paths specifically to keep them from ever being extracted: the official image's entrypoint skips extracting a bundled theme/plugin if something already occupies that destination path, so mounting an empty volume there is enough to suppress it. Follow this same pattern if a future WordPress version ships a new default theme (e.g. `twentytwentysix`) that needs suppressing too.
- `./php.ini` is intended to bind-mount a file to `/usr/local/etc/php/conf.d/uploads.ini` for upload size limits — note that Docker will silently create `php.ini` as a *directory* instead of failing if the file doesn't exist on the host when the container first starts, silently breaking this mount. If upload-size PHP settings don't seem to apply, check that `./php.ini` on disk is an actual file, not an empty directory.

### `.gitignore` structure for `htdocs/`

The ignore rules for `htdocs/wp-content/{themes,plugins,uploads}` follow a repeating "re-include the directory, then re-exclude its contents, then re-include one placeholder" pattern (needed because a `!`-negated directory does not implicitly un-ignore its own contents in git). If you add a new kept file under one of these directories, add the exclusion inside that directory's own block rather than at the top level — a bare `!/htdocs/wp-content/themes/foo` added elsewhere won't work if a broader `/htdocs/wp-content/themes/*` rule earlier in the file already re-ignored everything.

## Conventions

- Line endings are LF everywhere, enforced by `.gitattributes` (`* text=auto eol=lf`) — this deliberately overrides Git for Windows' system-level `core.autocrlf=true` and matches `.editorconfig` (`end_of_line = lf`, 2-space indent).
