# Contributing

Thank you for your interest in contributing to **moodle-tiny_justify**!

## How to Contribute

1. **Fork** the repository and create your branch from `main`.
2. Make your changes, following the coding style of the existing code.
3. Ensure your changes do not break existing functionality.
4. **Commit** your changes with a clear and descriptive message.
5. Open a **Pull Request** describing what you changed and why.

## Reporting Bugs

Please open an [issue](https://github.com/cte-zl-ifrn/moodle-tiny_justify/issues) with:
- A clear description of the bug
- Steps to reproduce it
- Expected vs. actual behaviour
- Your Moodle version and PHP version

## Suggesting Features

Open an [issue](https://github.com/cte-zl-ifrn/moodle-tiny_justify/issues) with the label `enhancement` and describe the feature you would like to see.

## Code Style

Follow the [Moodle coding style guidelines](https://moodledev.io/general/development/policies/codingstyle).

## Development Workflow (AVA/Docker)

When changing JavaScript under `amd/src/`, use this update flow before testing the editor UI:

1. Increase plugin version in `version.php`.
2. Ensure compiled files are available in `amd/build/`.
	- Standard Moodle workflow: `npx grunt amd --root=lib/editor/tiny/plugins/justify`
	- In environments without Node/Grunt inside the Moodle container, keep precompiled `amd/build/*.min.js` committed.
3. From AVA workspace root, run:

	```bash
	docker compose exec -T -w /var/www/html moodle php admin/cli/upgrade.php --non-interactive
	docker compose exec -T -w /var/www/html moodle php admin/cli/purge_caches.php
	```

4. Hard refresh the browser (`Ctrl+F5`) and verify the button appears in toolbar/menu/quick toolbar.

### Quick checklist when button is missing

- `amd/build/*.min.js` exists and matches source changes.
- `version.php` was bumped.
- `upgrade.php` and `purge_caches.php` were executed.
- TinyMCE plugin/button is enabled in Moodle admin settings.

## License

By contributing, you agree that your contributions will be licensed under the [GNU GPL v3 or later](LICENSE).
