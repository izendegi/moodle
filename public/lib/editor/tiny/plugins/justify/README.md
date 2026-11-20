# moodle-tiny_justify

Moodle TinyMCE justify plugin — adds a **Justify** button to the TinyMCE editor toolbar, allowing users to apply full text justification in Moodle content areas.

## Requirements

- Moodle 4.5 or later (TinyMCE editor)

## Installation

**Via Git:**

```bash
git clone https://github.com/cte-zl-ifrn/moodle-tiny_justify.git justify
```

Place the `justify` folder inside `/lib/editor/tiny/plugins/` in your Moodle installation directory.

**Via ZIP:**

1. Download the ZIP file from the [releases page](https://github.com/cte-zl-ifrn/moodle-tiny_justify/releases).
2. In Moodle, go to **Site Administration** → **Plugins** → **Install plugins** and upload the ZIP file.
3. Follow the on-screen instructions to complete the installation.

## Configuration

After installation, go to **Site Administration** → **Plugins** → **Text editors** → **TinyMCE editor** → **General settings** and ensure the Justify plugin is enabled in the toolbar configuration.

## Usage

Once installed and enabled, a **Justify** button will appear in the TinyMCE toolbar. Click it to apply full text justification to the selected text or current paragraph.

## Build and cache refresh (AVA/Docker)

If you change files in `amd/src/` (for example `plugin.js`, `commands.js`, or `configuration.js`), Moodle will only use the new code after:

1. having compiled files in `amd/build/`;
2. plugin upgrade (when version changes);
3. cache purge.

### 1) Bump plugin version

Update `version.php` with a new `$plugin->version` value.

### 2) Ensure `amd/build` exists

In standard Moodle development, compile with Grunt from Moodle root:

```bash
npx grunt amd --root=lib/editor/tiny/plugins/justify
```

In this AVA environment, the `moodle` container may not include `node/npx/grunt`. If so, keep the precompiled files in `amd/build/` committed to the plugin repository.

### 3) Run upgrade and purge caches in container

From the AVA workspace root:

```bash
docker compose exec -T -w /var/www/html moodle php admin/cli/upgrade.php --non-interactive
docker compose exec -T -w /var/www/html moodle php admin/cli/purge_caches.php
```

### 4) Browser hard refresh

Reload the editor page with a hard refresh (`Ctrl+F5`).

## Troubleshooting: button does not appear

If **Justify** does not appear in toolbar/menu/quick toolbar:

- Confirm `amd/build/*.min.js` exists for this plugin.
- Confirm plugin version in `version.php` was increased.
- Run `upgrade.php` and `purge_caches.php` again.
- Check TinyMCE settings in Moodle admin and ensure the plugin/button is enabled in the toolbar setup.

## License

This plugin is licensed under the [GNU GPL v3 or later](LICENSE).

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.
