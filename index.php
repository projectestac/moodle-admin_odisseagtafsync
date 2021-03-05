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
 * Tool to synchronize users between GTAF and Odissea. It download CSV files from
 * specified SFTP server and process them
 *
 * @package    tool
 * @subpackage odisseagtafsync
 * @copyright  2013-2021 Departament d'Ensenyament de la Generalitat de Catalunya
 * @author     Sara Arjona Téllez <sarjona@xtec.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once dirname(__FILE__) . '/../../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once dirname(__FILE__) . '/locallib.php';
require_once dirname(__FILE__) . '/classes/odissea_gtaf_synchronizer.class.php';
require_once dirname(__FILE__) . '/classes/odissea_uu_progress_tracker.class.php';
require_once dirname(__FILE__) . '/lib/sftp.class.php';
require_once dirname(__FILE__) . '/lib/odissea_log4p.class.php';

// admin_externalpage_setup calls require_login and checks moodle/site:config
admin_externalpage_setup('odisseagtafsync');

$header = get_string('pluginname', 'tool_odisseagtafsync');
$renderer = $PAGE->get_renderer('tool_odisseagtafsync');
$sync = optional_param('sync', false, PARAM_BOOL);

$synchro = new odissea_gtaf_synchronizer(false);

if ($sync) {
    $result = $synchro->synchro();
    echo $renderer->sync_page(1, $result, $synchro->errors);
} else {
    echo $renderer->header();
    echo $renderer->heading(get_string('pluginname', 'tool_odisseagtafsync'));
    echo $renderer->box(get_string('manualsyncdesc', 'tool_odisseagtafsync'));

    try {
        $files = $synchro->get_files_sftp();

        if (empty($files)) {
            echo $OUTPUT->notification('No hi ha fitxers al servidor SFTP');
        } else {
            echo '<strong>Fitxers disponibles al servidor:</strong>';
            echo '<ul>';
            foreach ($files as $file) {
                echo '<li>' . $file . '</li>';
            }
            echo '</ul>';
        }
    } catch (Exception $exception) {
        echo $renderer->error_text($exception->getMessage());
        echo '<br /><br />';
    }

    $pending = $synchro->get_files_pending();

    echo '<strong>Fitxers pendents d\'importar:</strong>';

    if (empty($pending)) {
        echo $OUTPUT->notification('No hi ha fitxers a la carpeta de fitxers per importar');
    } else {
        echo '<ul>';
        foreach ($pending as $filepath => $file) {
            echo '<li>' . $file . ' - ' . filesize($filepath) . ' bytes (' . round(filesize($filepath) / 1024) . ' kB)</li>';
        }
        echo '</ul>';
    }

    echo $OUTPUT->single_button('?sync=true', get_string('manualsync', 'tool_odisseagtafsync'));
    echo $renderer->footer();
}
