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
admin_externalpage_setup('gtafmove');

$renderer = $PAGE->get_renderer('tool_odisseagtafsync');

$header = get_string('pluginname', 'tool_odisseagtafsync');

$settings = get_config('tool_odisseagtafsync');

$returnurl = new moodle_url('/'. $CFG->admin . '/tool/odisseagtafsync/move.php');

$synchro = new odissea_gtaf_synchronizer();
$action = optional_param('action', false, PARAM_TEXT);
$filename = false;
if ($action) {
    $filename = required_param('file', PARAM_TEXT);
}
if ($action && $filename) {
    $confirm = optional_param('confirm', false, PARAM_BOOL);
    if ($confirm) {
        switch ($action) {
            case 'copy':
                $result = $synchro->restore_file($filename);
                echo $renderer->sync_page(2, $result, $synchro->errors);
                break;
            case 'delete':
                $result = $synchro->delete_file($filename);
                echo $renderer->sync_page(2, $result, $synchro->errors);
                break;
        }

    } else {
        $accio = $action == 'delete' ? 'suprimir' : 'copiar';
        echo $renderer->header();
        echo $renderer->heading(get_string('pluginname', 'tool_odisseagtafsync'));
        echo $renderer->box(get_string('managefiledesc', 'tool_odisseagtafsync'));
        $message = 'Estàs segur que vols '.$accio.' el fitxer '.$filename.'?';
        echo $OUTPUT->confirm($message, '?action='.$action.'&file='.$filename.'&confirm=true', "");
    }
} else {
    echo $renderer->header();
    echo $renderer->heading(get_string('pluginname', 'tool_odisseagtafsync'));
    echo $renderer->box(get_string('managefiledesc', 'tool_odisseagtafsync'));
    $files = $synchro->get_files_backup();
    $pending = $synchro->get_files_pending();

    echo '<strong>Fitxers pendents d\'importar:</strong>';
    if (empty($pending)) {
        echo $OUTPUT->notification('No hi ha fitxers a la carpeta de fitxers per importar');
    } else {
        echo '<ul>';
        foreach($pending as $filepath => $file) {
            echo '<li>'.$file;
            echo ' [<a href="?action=delete&file=' . $file . '">Elimina de la carpeta d\'importació de fitxers</a>]';
            $filesize = round(filesize($filepath)/1024);
            echo ' '.$filesize.'kB</li>';
        }
        echo '</ul>';
    }

    echo '<strong>Fitxers al directori de còpies de seguretat:</strong>';
    if (empty($files)) {
        echo $OUTPUT->notification('No hi ha fitxers a la carpeta de còpies de seguretat');
    } else {
        echo '<ul>';
        foreach ($files as $filepath => $file) {
            echo '<li>' . $file;
            if (!isset($pending[$file])) {
                echo ' [<a href="?action=copy&file=' . $file . '">Copia a la carpeta d\'importació</a>]';
            } else {
                echo ' [Ja existeix a la carpeta d\'importació]';
            }
            $filesize = round(filesize($filepath)/1024);
            echo ' '.$filesize.'kB</li>';
        }
        echo '</ul>';
    }
    echo $renderer->footer();
}


