# Changelog

This changelog records only the two latest **published** releases of `local_slow_queries`, verified on October 24, 2026. Each entry is based on the published Git tag compared with its immediately preceding published tag. Commits made after the October 19 `1.1.13` release, including `Mustache`, `Langs Order` and `-core_text`, are not included below.

## [1.1.13] - 2026-10-19

### Added
- Added optional Moodle MCP integration via `local_slow_queries\mcp\provider`, automatically discovered when `local_mcp` is installed, without introducing a required dependency or additional database tables.
- Added five administrator-only READ tools: `slowqueries_get_status`, `slowqueries_list_queries`, `slowqueries_get_query`, `slowqueries_analyse_query` and `slowqueries_get_occurrences`. They inspect existing logs, grouped SQL fingerprints, execution statistics, individual records, heuristic index suggestions and recent occurrences without executing captured SQL.
- Added the confirmed WRITE tool `slowqueries_save_comment`, allowing an administrator to save investigation notes associated with identical SQL text following an MCP preview and confirmation.
- Added a dismissible MCP integration notice for site administrators who have not installed `local_mcp`, with user-preference persistence, an authenticated POST action and a session-key check.
- Added English and Brazilian Portuguese language strings and regression tests for the MCP tools, comment handling and index helper.

### Security and reliability
- Required `moodle/site:config` in the system context for MCP operations, in addition to the appropriate MCP READ/WRITE scope.
- Kept potentially sensitive SQL text, bind parameters and full backtraces out of default responses; explicit options are needed to request them. Bounded result pages, time windows and text lengths.
- Restricted SQL analysis to existing heuristic index suggestions; no logged SQL, DDL, indexes, configuration changes or cleanup commands are executed by the MCP tools.
- Centralized native-web and MCP investigation-note updates in a shared service, with a maximum note length, a state hash, per-query locking and stale-write detection.
- Fixed cross-database lookup of identical SQL comments by using Moodle's portable TEXT comparison for the prefix and then verifying exact text in PHP.
- Added `sesskey` and expected comment-state fields to the native comment form, escaped the displayed note and rejected stale submissions.
- Guarded automatic index creation when its target table does not yet exist, allowing fresh Moodle installations to proceed before all optional/core tables are available.

### Documentation and maintenance
- Documented the MCP tools, permissions, sensitive-data options and confirmed comment workflow in `README.md`.
- Added historical Git-ref-scoped concurrency configuration to the release workflow.
- Updated the plugin version/release metadata for `1.1.13`.

**Full comparison:** [1.1.12...1.1.13](https://github.com/EduardoKrausME/moodle-local_slow_queries/compare/1.1.12...1.1.13)

## [1.1.12] - 2026-10-15

### CI and assets
- Updated the PostgreSQL CI service image from version 16 to 17.
- Updated the MariaDB CI service from version 10 to 11.4, adapting the container's environment variables and health check.
- Updated the plugin icon asset at `pix/icon.png`.

### Release metadata
- Updated the plugin version/release metadata for `1.1.12`. No application logic files changed between these two tags.

**Full comparison:** [1.1.11...1.1.12](https://github.com/EduardoKrausME/moodle-local_slow_queries/compare/1.1.11...1.1.12)
