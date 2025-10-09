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
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */


namespace report_fundae;

use core_reportbuilder\local\helpers\database;
use html_writer;

/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

class db {

    /**
     * Takes a string with placeholders and converts it to the SQL expression
     *
     * Examples:
     *     list($sql1, $params1) = \tool_reportbuilder\db::sql_get_string('and', 'moodle',
     *         ['one' => 't.field1', 'two' => 't.field2']);
     *     list($sql2, $params2) = \tool_reportbuilder\db::sql_get_string('backto', 'moodle',
     *         't.url');
     *
     *     $sql1:    "'' || t.field1 || :wpdbp6 || t.field2"
     *     $params1: ['wpdbp6' => ' and ']
     *     $sql2:    "'' || :wpdbp7 || t.url"
     *     $params2: ['wpdbp7' => 'Back to ']
     *
     * @param string $identifier string identifier
     * @param string $component string component
     * @param array|object|string $asql similar to $a attribute for the string but instead of values in has the SQL
     *        field names (or expressions). If the string takes simple placeholder ({$a}), this is the string with the
     *        SQL field (expression).
     * @param bool $ascsv return as CSV string with only necessary fields, no other characters, no concatenation
     *        (to be used in group by)
     * @return array|string
     */
    public static function sql_get_string(string $identifier, string $component, $asql, bool $ascsv = false) {
        if (is_array($asql) || is_object($asql)) {
            $asql = (array)$asql;
            // This is a string with multiple placeholders, i.e.: $string['and'] = '{$a->one} and {$a->two}'; .
            $placeholders = array_map(function($p) {
                return '{$a->' . $p . '}';
            }, array_keys($asql));
            $a = (object)array_combine(array_keys($asql), $placeholders);
            $phmap = array_combine($placeholders, array_values($asql));
        } else {
            // This is a string with a simple placeholder, i.e.: $string['backto'] = 'Back to {$a}'; .
            $a = '{$a}';
            $phmap = ['{$a}' => $asql];
        }

        // Evaluate the string but replace all parameters with {$a->paramname}, it will pretty much restore
        // the string as it was defined in the language file.
        $str = get_string($identifier, $component, $a);
        return self::sql_string_with_placeholders($str, $phmap, $ascsv);
    }

    /**
     * Correct implementation of sql_fullname
     *
     * @param string $usertablealias
     * @param bool $override
     * @param bool $ascsv return as CSV string with only necessary fields, no other characters, no concatenation
     *        (to be used in group by and/or sorting)
     * @return array|string
     */
    public static function sql_fullname($usertablealias = 'u', bool $override = false, bool $ascsv = false) {
        global $DB;
        // All possible user names.
        $usernamefields = array_values(\core_user\fields::get_name_fields());

        // Placeholders that we are going to use for user names.
        $placeholders = array_map(function($k) {
            return '{$a->' . $k . '}';
        }, range(0, count($usernamefields) - 1));

        // SQL expressions for each user name.
        // To account for fields configured as fullname format being NULL, we need to COALESCE each of them. Note that to avoid
        // limitations in Oracle regarding empty string and NULL equality, in addition to avoiding the use of parameters in the
        // returned expression, we set the second argument to ' ' (string with one space).
        $prefix = strlen($usertablealias) ? $usertablealias . '.' : '';
        $sqlfieldcoalesce = ($DB->get_dbfamily() == 'oracle' ? ' ' : '');
        $sqlfields = array_map(function($p) use ($prefix, $sqlfieldcoalesce, $ascsv) {
            return $ascsv ? "{$prefix}{$p}" : "COALESCE({$prefix}{$p}, '{$sqlfieldcoalesce}')";
        }, $usernamefields);

        // Create a string where instead of all parts of user names we have placeholders.
        // For example, "firstname lastname" will become "{$a->4} {$a->5}".
        $user = (object)array_combine($usernamefields, $placeholders);
        $str = fullname($user, $override);

        // Create a map of placeholders to SQL expressions, for example:
        // ['{$a->4}' => "COALESCE('t.firstname','')", ...].
        $phmap = array_combine($placeholders, $sqlfields);
        // Return the SQL for a string with placeholders.
        return self::sql_string_with_placeholders($str, $phmap, $ascsv);
    }

    /**
     * Generate SQL query and params for creating concatenated tag field
     *
     * @param string $tagfield
     * @param string $tagtablealias
     * @param string $tablealias
     * @param string $component
     * @return array
     */
    public static function sql_tag_query(string $tagfield, string $tagtablealias, string $tablealias,
            string $component) : array {

        $taginstancealias = database::generate_alias();

        $itemtypeparam = database::generate_param_name();
        $componentparam = database::generate_param_name();

        $sql = "(SELECT {$tagfield}
                   FROM {tag_instance} {$taginstancealias}
                   JOIN {tag} {$tagtablealias} ON {$tagtablealias}.id = {$taginstancealias}.tagid
                  WHERE {$taginstancealias}.itemid = {$tablealias}.id
                    AND {$taginstancealias}.itemtype = :{$itemtypeparam}
                    AND {$taginstancealias}.component = :{$componentparam})";

        $params[$itemtypeparam] = $component;
        $params[$componentparam] = $component;

        return [$sql, $params];
    }

    /**
     * Return SQL query and params for a concatenated tag field
     *
     * @param string $tablealias
     * @param string $component
     * @return array
     */
    public static function sql_tag_field(string $tablealias, string $component) : array {
        $tagalias = database::generate_alias();

        $placeholder = html_writer::span('{{name}}', '', ['data-rawname' => '{{rawname}}']);
        list($placeholdersql, $placeholderparams) = self::sql_string_with_placeholders($placeholder, [
            '{{name}}' => "{$tagalias}.name",
            '{{rawname}}' => "{$tagalias}.rawname",
        ]);

        $taggroupconcat = self::sql_group_concat($placeholdersql, '', "{$tagalias}.name");
        list($sql, $params) = self::sql_tag_query($taggroupconcat, $tagalias, $tablealias, $component);

        return [$sql, $params + $placeholderparams];
    }

    /**
     * Return SQL query and params for a concatenated tag filter
     *
     * @param string $tablealias
     * @param string $component
     * @return array
     */
    public static function sql_tag_filter(string $tablealias, string $component) : array {
        $tagalias = database::generate_alias();
        $tagfield = self::sql_group_concat("{$tagalias}.name", '|', "{$tagalias}.name");

        return self::sql_tag_query($tagfield, $tagalias, $tablealias, $component);
    }

    /**
     * Remove Oracle hack
     *
     * Oracle does not allow to do $DB->sql_concat('x', $DB->sql_concat('a', 'b'))
     *
     * Each sql_concat wraps the expression in MOODLELIB.UNDO_MEGA_HACK()
     * If we want to use one concat inside another, we need to remove the mega hack from the inner one.
     *
     * @param string $sql
     * @return string
     */
    public static function remove_oracle_hack(string $sql) {
        global $DB;
        if ($DB->get_dbfamily() === 'oracle' &&
                preg_match('/^\s*MOODLELIB.UNDO_MEGA_HACK\((.*)\)\s*$/', $sql, $matches)) {
            return ' ' . $matches[1] . ' ';
        }
        return $sql;
    }

    /**
     * Converts a string with placeholders to an SQL expression
     *
     * Examples:
     *     list($sql1, $params) = db::sql_string_with_placeholders('{p2} and {p1}',
     *         ['{p1}' => 't.field1', '{p2}' => 't.field2'],
     *         false);
     *     $sql2 = db::sql_string_with_placeholders('{p1} and {p2}',
     *         ['{p1}' => 't.field1', '{p2}' => 't.field2'],
     *         true);
     *
     *     $sql1:   "'' || t.field2 || :wpdbp5 || t.field1" (different syntax for different DB types)
     *     $params: ['wpdbp5' => ' and ']
     *     $sql2:   "t.field2, t.field1"
     *
     * @param string $str
     * @param array $phmap placeholders mapping - all placeholders that can be in strings mapped to the SQL
     *        field names (or expressions)
     * @param bool $ascsv
     * @return array|string
     */
    public static function sql_string_with_placeholders(string $str, array $phmap, bool $ascsv = false) {
        global $DB;

        // Regex that finds all placeholders in the string.
        $regex = join('|', array_map(function($p) {
            return preg_quote($p, '/');
        }, array_keys($phmap)));

        // Parts of the string between placeholders.
        $parts = preg_split("/($regex)/", $str);

        // List of placeholders in the order they appear in the string.
        preg_match_all("/($regex)/", $str, $matches);
        $placeholders = $matches[1];
        // Now the size of array $parts is one bigger than the size of array $placeholders.

        $elements = [];
        $params = [];
        foreach ($parts as $i => $part) {
            if ($i) {
                $elements[] = $phmap[$placeholders[$i - 1]];
            }
            if (strlen($part) && !$ascsv) {
                if (preg_match('/^[ \,\.\-\(\)]*$/', $part)) {
                    // This part is a simple string containing spaces, commas, braces, we don't need parameter.
                    $elements[] = "'" . $part . "'";
                } else {
                    // Use parameter for any complex string value.
                    $paramname = database::generate_param_name();
                    $params[$paramname] = $part;
                    $elements[] = ':' . $paramname;
                }
            }
        }

        if ($ascsv) {
            return join(', ', $elements);
        } else {
            $sql = call_user_func_array([$DB, 'sql_concat'], $elements);
            return [$sql, $params];
        }
    }

    /**
     * Generate SQL expression for sorting group concatenated fields
     *
     * @param string $field The original field or SQL expression
     * @param string $sort A valid SQL ORDER BY to sort the concatenated fields, if omitted then $field will be used
     * @return string
     */
    private static function sql_group_concat_sort(string $field, string $sort = null) {
        global $DB;

        // Fallback to sorting by the specified field, unless it contains parameters which would be duplicated.
        if ($sort === null && !preg_match('/[:?$]/', $field)) {
            $sort = $field;
        }

        switch ($DB->get_dbfamily()) {
            case 'mssql':
                return $sort ? "WITHIN GROUP (ORDER BY {$sort})" : '';
                break;
            case 'mysql':
                return $sort ? "ORDER BY {$sort}" : '';
                break;
            case 'postgres':
                // Extract sort field and direction.
                preg_match('/(?<direction>ASC|DESC)?$/i', $sort, $matches);
                $direction = $matches['direction'] ?? '';
                $sort = \core_text::substr($sort, 0, \core_text::strlen($sort) - \core_text::strlen($direction));
                return $sort ? "ORDER BY CAST({$sort} AS VARCHAR) $direction" : '';
                break;
            case 'oracle':
                // Oracle requires the WITHIN keyword (default to 1 if $sort is empty).
                return 'WITHIN GROUP (ORDER BY ' . ($sort ?: '1') . ')';
                break;
        }
    }

    /**
     * SQL expression for "group concatenation" aggregation
     *
     * @param string $field database field or SQL expression that needs to be concatenated. Note that MSSQL
     *        can not do aggregation functions on sub-selects
     * @param string $separator
     * @param string $sort
     * @return string
     */
    public static function sql_group_concat(string $field, string $separator = null, string $sort = null): string {
        global $DB;

        // If $separator is not specified, default to the helper method to specify.
        $separator = $separator ?? helper::get_list_separator();

        $fieldsort = self::sql_group_concat_sort($field, $sort);
        switch ($DB->get_dbfamily()) {
            case 'mssql':
                return "STRING_AGG({$field}, '{$separator}') {$fieldsort}";
                break;
            case 'postgres':
                return "STRING_AGG(CAST({$field} AS VARCHAR), '{$separator}' {$fieldsort})";
                break;
            case 'mysql':
                return "GROUP_CONCAT({$field} {$fieldsort} SEPARATOR '{$separator}')";
                break;
            case 'oracle':
                return "LISTAGG({$field}, '{$separator}') {$fieldsort}";
                break;
            default:
                return '';
        }
    }

    /**
     * SQL expression for "group concatenation distinct" aggregation
     *
     * Note! Only MySQL/Postgres currently support "distinct" in group concatenation, empty string is returned for other DB's
     *
     * @param string $field database field or SQL expression that needs to be concatenated. Note that MSSQL
     *        can not do aggregation functions on sub-selects
     * @param string $separator
     * @param string $sort
     * @return string
     */
    public static function sql_group_concat_distinct(string $field, string $separator = null, string $sort = null): string {
        global $DB;

        // If $separator is not specified, default to the helper method to specify.
        $separator = $separator ?? helper::get_list_separator();

        $fieldsort = self::sql_group_concat_sort($field, $sort);
        switch ($DB->get_dbfamily()) {
            case 'postgres':
                // Postgres cannot sort by columns in an aggregate method with distinct.
                if (!empty($fieldsort) && (strpos($field, '||') !== false)) {
                    // Fallback to sorting by the original field, preserving direction.
                    preg_match('/(?<direction>ASC|DESC)?$/i', $fieldsort, $matches);
                    $direction = $matches['direction'] ?? '';
                    $fieldsort = self::sql_group_concat_sort('', "{$field} {$direction}");
                }

                return "STRING_AGG(DISTINCT CAST({$field} AS VARCHAR), '$separator' {$fieldsort})";
                break;
            case 'mysql':
                return "GROUP_CONCAT(DISTINCT {$field} {$fieldsort} SEPARATOR '$separator')";
                break;
        }

        return '';
    }
}
