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

/**
 * Sync with Gtaf database
 */
class sync extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('manualsync', 'tool_odisseagtafsync');
    }

    /**
     * Performs the sync
     */
    public function execute() {
        global $CFG;

        $settings = get_config('tool_odisseagtafsync');

        if (empty($settings->ftphost)) {
            return;
        }

        mtrace('odisseagtafsync: tool_odisseagtafsync_cron() started at '. date('H:i:s'));
        try {
            require_once ($CFG->dirroot.'/admin/tool/odisseagtafsync/locallib.php');
            $synchro = new \odissea_gtaf_synchronizer(true);
            $results = $synchro->synchro();
            if (!empty($synchro->errors)) {
                foreach($synchro->errors as $file => $error) {
                    mtrace($file.': '.$error);
                }
            }

            if (!empty($results)) {
                foreach ($results as $result) {
                    if (!empty($result)) {
                        mtrace($result);
                    }
                }
            } else {
                mtrace(get_string('nosyncfiles', 'tool_odisseagtafsync'));
            }
        } catch (Exception $e) {
            mtrace('odisseagtafsync: tool_odisseagtafsync_cron() failed with an exception:');
            mtrace($e->getMessage());
            mtrace($e->getTraceAsString());
        }
        mtrace('odisseagtafsync: tool_odisseagtafsync_cron() finished at ' . date('H:i:s'));
    }
}
