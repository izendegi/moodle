# local_slow_queries — Slow Queries Viewer for Moodle

`local_slow_queries` is an admin-only plugin that turns your `mdl_log_queries` table into a practical UI to **find, triage and analyze slow SQL statements** executed by Moodle.

It focuses on three daily workflows:

- **List & filter** slow queries quickly (default `exectime > 3s`).

> Access is restricted to: `require_capability("moodle/site:config", context_system::instance())`

## What this plugin assumes

This plugin **does not capture queries by itself**. It assumes your environment already logs queries into:

- `mdl_log_queries` (or `{log_queries}` with Moodle prefix)

So the plugin is a **viewer + analysis helper** for an existing slow-query logging mechanism.

## Data source

The plugin reads from:

### Table: `mdl_log_queries`

Fields used by the UI:

- `id` — unique identifier
- `qtype` — query type (stored by your logger)
- `sqltext` — SQL statement text
- `sqlparams` — parameters (string representation)
- `error` — non-zero marks error
- `info` — optional extra data
- `backtrace` — string backtrace
- `exectime` — decimal seconds
- `timelogged` — unix timestamp

Index suggestions in the `install.xml` are focused on viewer performance:
- `timelogged`, `exectime`, `qtype`, `error`

## Backtrace "caller" logic

The list view shows a simplified "caller" derived from backtrace:

1. Find the first frame containing `/lib/dml/`
2. Show the **next frame** (usually the Moodle component that invoked the DB layer)
3. Shorten long absolute paths to keep it readable in a table

This is intentionally pragmatic: it helps you quickly identify whether the query comes from
completion, search indexing, forum reports, etc.

## CRON detection

A query is considered "CRON" when its backtrace contains:

- `/admin/cli/cron.php`

The UI then:
- sets the CRON column ✅
- adds a CRON badge

## Parameter handling

`sqlparams` in production can vary depending on the logger implementation.
This plugin attempts to parse common formats:

- JSON arrays/objects
- PHP `serialize()` format
- "var_export-like" output (example: `array ( 0 => 4, 1 => 50, )`)

Then it applies parameters to SQL by replacing positional `?` placeholders sequentially.

Notes:
- This is a **best-effort representation** for analysis and reproduction.
- It is not intended to be a perfect SQL reconstitution for every edge case.

## UX & UI choices

This plugin intentionally keeps a **light and readable look**:

- Cards with rounded corners
- Soft shadows
- Badges for quick categorization (CRON / WEB / ERROR)
- Progress bars for distribution/share reports

It relies on Moodle’s loaded Bootstrap styles and adds minimal SCSS.

## Security model

- Pages are accessible only to users with:
    - `moodle/site:config` in `context_system`

This is critical because the plugin can reveal:
- raw SQL
- parameters (potentially containing sensitive values)
- file paths in backtraces

## Typical usage patterns

### Ops triage
- Open `index.php`
- Filter `exectime >= 10`
- Identify CRON-heavy spikes or web-timeouts
- Open details, extract candidate indexes

### Regression monitoring
- Check last 7 days volume
- Compare max/avg trends weekly
- Identify new callers (components) from backtrace changes
