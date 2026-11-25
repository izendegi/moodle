<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * CLI script to bulk change the 'showsinglestudentbar' setting of the Completion
 * Progress block instances in the courses of a category.
 *
 * @package    block_completion_progress
 * @copyright  2026 Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

// Setting changed by this script.
$setting = 'showsinglestudentbar';

[$options, $unrecognised] = cli_get_params(
    [
        'category' => null,
        'value' => null,
        'unset' => false,
        'nosubcategories' => false,
        'execute' => false,
        'help' => false,
    ],
    ['h' => 'help']
);

if ($unrecognised) {
    cli_error('Unrecognised options: ' . implode(', ', $unrecognised));
}

$help = "Bulk change the 'showsinglestudentbar' setting (show the only student's bar) of the Completion
Progress block instances in the courses of a category.

By default the script runs in dry-run mode and only reports what it would change.

Options:
  --category=ID        Course category id (required).
  --value=0|1          New value (1 = Show, 0 = Hide). Required unless --unset is given.
  --unset              Remove the setting from the instances, so they follow the site default.
  --nosubcategories    Only courses directly in the category (default: include subcategories).
  --execute            Apply the changes (otherwise dry-run).
  -h, --help           Print this help.

Examples:
  php blocks/completion_progress/cli/bulk_set_config.php --category=12 --value=1
  php blocks/completion_progress/cli/bulk_set_config.php --category=12 --value=1 --execute
  php blocks/completion_progress/cli/bulk_set_config.php --category=12 --unset --execute
";

if ($options['help']) {
    echo $help;
    exit(0);
}

// Validate options.
if (empty($options['category']) || !is_numeric($options['category'])) {
    cli_error("--category=ID is required.\n\n" . $help);
}
if ($options['unset']) {
    if ($options['value'] !== null) {
        cli_error('--value and --unset cannot be used together.');
    }
    $newvalue = null;
} else {
    if (!in_array((string)$options['value'], ['0', '1'], true)) {
        cli_error("--value=0|1 is required (or use --unset).\n\n" . $help);
    }
    $newvalue = (int)$options['value'];
}

$categoryid = (int)$options['category'];
$execute = !empty($options['execute']);

$category = $DB->get_record('course_categories', ['id' => $categoryid]);
if (!$category) {
    cli_error("Category {$categoryid} does not exist.");
}
$catcontext = context_coursecat::instance($categoryid);

// Find the block instances on course pages of the category (and subcategories).
$params = [
    'blockname' => 'completion_progress',
    'courselevel' => CONTEXT_COURSE,
];
if ($options['nosubcategories']) {
    $where = 'c.category = :categoryid';
    $params['categoryid'] = $categoryid;
} else {
    $where = $DB->sql_like('ctx.path', ':path');
    $params['path'] = $catcontext->path . '/%';
}

$sql = "SELECT bi.id, bi.configdata, c.id AS courseid, c.shortname
          FROM {block_instances} bi
          JOIN {context} ctx ON ctx.id = bi.parentcontextid AND ctx.contextlevel = :courselevel
          JOIN {course} c ON c.id = ctx.instanceid
         WHERE bi.blockname = :blockname
           AND {$where}
      ORDER BY c.id, bi.id";

$valuelabel = function ($value) {
    if ($value === null) {
        return '(not set: site default)';
    }
    return $value ? '1 (Show)' : '0 (Hide)';
};

cli_heading(($execute ? 'Applying' : 'Dry-run') . " '{$setting}' => " . $valuelabel($newvalue) .
    " in category {$categoryid} (" . format_string($category->name) . ')' .
    ($options['nosubcategories'] ? ' without subcategories' : ' and subcategories'));

$total = 0;
$changed = 0;
$transaction = $execute ? $DB->start_delegated_transaction() : null;

$rs = $DB->get_recordset_sql($sql, $params);
foreach ($rs as $rec) {
    $total++;
    $config = empty($rec->configdata) ? new stdClass() : unserialize_object(base64_decode($rec->configdata));
    if (!is_object($config)) {
        $config = new stdClass();
    }

    $oldvalue = (isset($config->$setting) && $config->$setting !== '') ? (int)$config->$setting : null;
    if ($oldvalue === $newvalue) {
        continue;
    }

    if ($newvalue === null) {
        unset($config->$setting);
    } else {
        $config->$setting = $newvalue;
    }

    mtrace("Course {$rec->courseid} ({$rec->shortname}), block instance {$rec->id}: " .
        $valuelabel($oldvalue) . ' -> ' . $valuelabel($newvalue));

    if ($execute) {
        $DB->update_record('block_instances', (object)[
            'id' => $rec->id,
            'configdata' => base64_encode(serialize($config)),
            'timemodified' => time(),
        ]);
    }
    $changed++;
}
$rs->close();

if ($transaction) {
    $transaction->allow_commit();
}

mtrace('');
mtrace("Block instances found: {$total}. " . ($execute ? 'Changed' : 'Would change') . ": {$changed}.");
if (!$execute && $changed) {
    mtrace('Run again with --execute to apply the changes.');
}
exit(0);
