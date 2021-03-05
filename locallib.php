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
 * Utility functions.
 *
 * @package    tool
 * @subpackage odisseagtafsync
 * @copyright  2013 Departament d'Ensenyament de la Generalitat de Catalunya
 * @author     Sara Arjona Téllez <sarjona@xtec.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once $CFG->dirroot . '/admin/tool/uploaduser/locallib.php';
require_once $CFG->dirroot . '/group/lib.php';
require_once $CFG->dirroot . '/cohort/lib.php';

/**
 * Validation callback function - verified the column line of csv file.
 * Converts column names to lowercase too.
 * @param $columns
 * @return bool|string
 * @throws coding_exception
 */
function validate_user_upload_columns(&$columns) {
    global $STD_FIELDS, $PRF_FIELDS;

    if (count($columns) < 2) {
        return get_string('csvfewcolumns', 'error');
    }

    // test columns
    $processed = array();
    foreach ($columns as $key => $unused) {
        $columns[$key] = trim(strtolower($columns[$key])); // no unicode expected here, ignore case
        $field = $columns[$key];
        if (!in_array($field, $STD_FIELDS) && !in_array($field, $PRF_FIELDS) && // if not a standard field and not an enrolment field, then we have an error
                !preg_match('/^course\d+$/', $field) && !preg_match('/^group\d+$/', $field) &&
                !preg_match('/^type\d+$/', $field) && !preg_match('/^role\d+$/', $field)) {
            return get_string('invalidfieldname', 'error', $field);
        }
        if (in_array($field, $processed)) {
            return get_string('csvcolumnduplicates', 'error');
        }
        $processed[] = $field;
    }
    return true;
}

/**
 *
 * @return array auth_name=>auth_string pairs with enabled auths ('manual', 'nologin', 'saml2')
 * @throws coding_exception
 */
function odissea_uu_supported_auths (): array {
    $plugins = get_enabled_auth_plugins();

    $choices = [];
    foreach ($plugins as $plugin) {
        $choices[$plugin] = get_string('pluginname', "auth_{$plugin}");
    }

    return $choices;
}

/**
 * @param string $file
 * @param string $folder
 * @return string
 */
function get_filename_withoutrepeat ($file, $folder): string {
    $i = 1;
    $filename = $file;
    $fileext = substr($file, strrpos($file, '.'));
    while (file_exists($folder. '/' . $filename)) {
        if (substr($filename, strlen($filename) - 5, 1) != ')') {
            $filename = substr($filename, 0, strlen($filename) - 4) . '(' . $i . ')'.$fileext;
        } else {
            $j = 6;
            while (substr($filename, strlen($filename) - ($j + 1), 1) != '(') {
                $j++;
            }
            $filename = substr($filename, 0, strlen($filename) - $j) . $i . ')'.$fileext;  // need upgrade
        }
        $i++;
    }
    return $filename;
}
