# local_slow_queries - Slow Queries Viewer for Moodle

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

- `id` - unique identifier
- `qtype` - query type (stored by your logger)
- `sqltext` - SQL statement text
- `sqlparams` - parameters (string representation)
- `error` - non-zero marks error
- `info` - optional extra data
- `backtrace` - string backtrace
- `exectime` - decimal seconds
- `timelogged` - unix timestamp

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

## Optional MCP integration (ChatGPT)

If [local_mcp](https://eduardokrausme.github.io/moodle-local_mcp/) is installed,
its discovery mechanism loads `local_slow_queries\mcp\provider` from
`classes/mcp/provider.php`. This integration is optional: it adds no extra
server process, no database tables and no hard dependency in `version.php`.
The plugin still depends on Moodle's existing `log_queries` logging table;
MCP does not enable `logslow` itself.

| Tool | MCP scope | Description |
| --- | --- | --- |
| `slowqueries_get_status` | READ | Check `logslow` configuration and recent query counts, error counts, mean and maximum execution time. |
| `slowqueries_list_queries` | READ | List grouped SQL fingerprints, execution counts, average times, CRON status and caller, with search and pagination. |
| `slowqueries_get_query` | READ | Inspect one logged execution, average time, comment and optionally SQL, parameters and backtrace. |
| `slowqueries_analyse_query` | READ | Return existing heuristic index suggestions for a logged query, without running logged SQL. |
| `slowqueries_get_occurrences` | READ | Retrieve recent occurrences of the exact logged SQL with timestamps, durations and error indicators. |
| `slowqueries_save_comment` | WRITE | Confirm an investigation note for all records sharing identical SQL text. |

### Permissions and sensitive information

Every tool requires `moodle/site:config` in `context_system`, matching
the native administration report. The MCP token must also grant READ or WRITE
scope, as appropriate. **Slow query text, bind parameters and backtraces can
contain passwords, tokens, personal data and private file paths.** For this
reason, list results include SHA-256 SQL fingerprints rather than SQL text
by default. `slowqueries_get_query` also omits raw SQL, bind parameters and
full backtraces by default. An administrator must explicitly set
`include_sql=true` and/or `include_sensitive=true` to retrieve them.
Only use those options when genuinely needed, and handle the output as
sensitive administrative information.

READ tools do not alter Moodle data. They never execute captured SQL:
`slowqueries_analyse_query` uses the existing read-only
`index_suggestion_service::suggest()` heuristic, not an unrestricted SQL
execution interface, EXPLAIN ANALYZE or DDL. Index proposals are **not**
executed, and must be inspected and tested on a staging environment before
any administrator chooses to apply them manually. MCP cannot create indexes,
purge SQL logs, modify logging settings or run arbitrary SQL.

Results are capped at 40 groups or occurrences per page, with configurable
minimum execution duration and search text of up to 200 characters. The
status and occurrence windows are bounded to 1–30 days; SQL previews
are limited to 500 characters on search pages or 12,000 characters in
details. Returned parameters and backtrace are also truncated.

### Confirmed comment workflow

1. Use `slowqueries_list_queries` to identify a SQL fingerprint and the
   `sample_query_id`.
2. Call `slowqueries_get_query` for that ID. Record the
   `comment_state_hash` returned with its investigation comment.
3. Call `slowqueries_save_comment` with `query_id`, `comment`, and
   `expected_state_hash` from the prior read. Review the generated
   preview and confirm the WRITE request via local_mcp.
4. A Moodle lock serializes comment changes on identical SQL. The
   server checks the state hash again on execution, rejects conflicts,
   updates the shared comment and returns a new hash. Comments never
   modify the logged SQL or its parameter values.

The native comment form also uses this shared service: it requires a
Moodle session key, escapes displayed comments, and refuses to overwrite a
more recent edit. No `upgrade.php` change is required.

When the MCP provider changes, refresh the ChatGPT MCP
connection so the new tools are discovered.
