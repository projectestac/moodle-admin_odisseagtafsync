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
 * specified FTP server and process them
 *
 * @package    tool
 * @subpackage odisseagtafsync
 * @copyright  2013 Departament d'Ensenyament de la Generalitat de Catalunya
 * @author     Sara Arjona Téllez <sarjona@xtec.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(__FILE__) . '/locallib.php');

// admin_externalpage_setup calls require_login and checks moodle/site:config
admin_externalpage_setup('odisseagtafsync');

$renderer = $PAGE->get_renderer('tool_odisseagtafsync');

$header = get_string('pluginname', 'tool_odisseagtafsync');

$synchro = new odissea_gtaf_synchronizer();
$sync = optional_param('sync', false, PARAM_BOOL);
if ($sync) {
    $result = $synchro->synchro();
    echo $renderer->sync_page(1, $result, $synchro->errors);
} else {
    echo $renderer->header();
    echo $renderer->heading(get_string('pluginname', 'tool_odisseagtafsync'));
    echo $renderer->box(get_string('manualsyncdesc', 'tool_odisseagtafsync'));

    $files = $synchro->get_files_ftp();
    if (empty($files)) {
        echo $OUTPUT->notification('No hi ha fitxers al FTP');
    } else {
        echo '<strong>Fitxers disponibles al servidor:</strong>';
        echo '<ul>';
        foreach($files as $file) {
            echo '<li>'.$file.'</li>';
        }
        echo '</ul>';
    }

    $pending = $synchro->get_files_pending();
    echo '<strong>Fitxers pendents d\'importar:</strong>';
    if (empty($pending)) {
        echo $OUTPUT->notification('No hi ha fitxers a la carpeta de fitxers per importar');
    } else {
        echo '<ul>';
        foreach($pending as $filepath => $file) {
            $filesize = round(filesize($filepath)/1024);
            echo '<li>'.$file.' '.$filesize.'kB</li>';
        }
        echo '</ul>';
    }

    echo $OUTPUT->single_button('?sync=true', get_string('manualsync', 'tool_odisseagtafsync'));
    echo $renderer->footer();
}
