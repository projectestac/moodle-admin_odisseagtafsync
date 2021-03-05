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
 *  Sync with Gtaf database.
 *
 * @package    tool_odisseagtafsync
 * @copyright  2016 Pau Ferrer <pferre22@xtec.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_odisseagtafsync\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Sync with Gtaf database
 */
class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task.
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_name(): string {
        return get_string('manualsync', 'tool_odisseagtafsync');
    }

    /**
     * Performs the sync
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function execute() {

        global $CFG;

        try {
            require_once $CFG->dirroot . '/admin/tool/odisseagtafsync/locallib.php';
            require_once $CFG->dirroot . '/admin/tool/odisseagtafsync/classes/odissea_gtaf_synchronizer.class.php';
            require_once $CFG->dirroot . '/admin/tool/odisseagtafsync/classes/odissea_uu_progress_tracker.class.php';
            require_once $CFG->dirroot . '/admin/tool/odisseagtafsync/lib/sftp.class.php';
            require_once $CFG->dirroot . '/admin/tool/odisseagtafsync/lib/odissea_log4p.class.php';

            $synchro = new \odissea_gtaf_synchronizer(true);
            $results = $synchro->synchro();

            if (!empty($synchro->errors)) {
                foreach ($synchro->errors as $file => $error) {
                    mtrace($file . ': ' . $error);
                }
            }

            if (!empty($results)) {
                mtrace('Number of files processed: ' . count($results));
            } else {
                if (empty($synchro->errors)) {
                    mtrace(get_string('nosyncfiles', 'tool_odisseagtafsync'));
                }
            }
        } catch (Exception $e) {
            mtrace('odisseagtafsync: Cron execution failed with an exception: ');
            mtrace($e->getMessage());
            mtrace($e->getTraceAsString());
        }

    }
}
