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
 * Odissea-GTAF synchronization strings.
 *
 * @package    tool
 * @subpackage odisseagtafsync
 * @copyright  2013 Departament d'Ensenyament de la Generalitat de Catalunya
 * @author     Sara Arjona Téllez <sarjona@xtec.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Odissea-GTAF Synchronization';

$string['backtoindex'] = 'Back';
$string['configheader'] = 'SFTP settings';
$string['createpatherror'] = 'Folder {$a} can\'t be created';
$string['defaultcsvheader'] = 'Import CSV settings';
$string['defaultuserheader'] = 'Users default settings';
$string['flatfilenotenabled'] = 'The \'Flat file (CSV)\' enrol plugin is not enabled. It\'s necessary to enable it to process pending files.';
$string['sftpconnectionerror'] = 'Unable to connect to specified SFTP server.';
$string['sftpdirlisterror'] = 'Unable to get the list of files from specified SFTP server.';
$string['sftphost'] = 'SFTP server';
$string['inputpath'] = 'SFTP folder';
$string['inputpath_help'] = 'SFTP server folder where to place input data files which will be downloaded and processed in Moodle';
$string['mailerror'] = 'There was an error during the synchronization between Odissea and GTAF while the file <b>{$a->filename}</b> was processing<br/><br/>Warnings: {$a->warnings}<br/>Errors: {$a->errors}<br/><br/><br/>';
$string['mailerrorfile'] = 'There was an error during the synchronization between Odissea and GTAF while the file {$a->filename} was processing\nError: {$a->error}';
$string['mailsubject'] = 'Error during the Odissea-GTAF synchronization';
$string['manualnotenabled'] = 'The \'Manual\' enrol plugin is not enabled. It\'s necessary to enable it to process pending files.';
$string['manualsyncheader'] = 'Manual synchronization';
$string['manualsyncdesc'] = 'Let starts manually the synchronization process.<br/><br/>';
$string['manualsync'] = 'Sync';
$string['managefiledesc'] = 'Let move a file from backup folder to pending files directory.<br/><br/>';
$string['managefiles'] = 'Manage files';
$string['nosyncfiles'] = 'There is no pending file to process.';
$string['outputfolderpath'] = 'Moodle folder';
$string['outputfolderpath_help'] = 'Moodle folder where to download SFTP files, save log... <ul><li>SFTP files are downloaded to "pending" folder and remain there until they\'re processed.</li><li>Backup files are saved in "backup" folder before processing them.</li><li>Log files are created after every sync execution and stored in "results" directory.</li></ul>';
$string['paramsdesc'] = 'Settings parameters of the SFTP server, default users configuration, CSV import values...';
$string['password'] = 'SFTP password';
$string['preparedenrolmentsok'] = 'The content of this file has been moved to the location specified in <b>Site administration | Plugins | Enrolments | Flat file (CSV) | File location (<i>enrol_flatfile | location</i>)</b> to process the unenrolments the following time the cron will be executed.';
$string['processrestorefileok'] = 'The specified file has been copied correctly to the import folder.';
$string['preparedenrolmentsok_cron'] = 'The content of this file has been moved to the paht {$a} to process the unenrolments the following time the cron will be executed.';
$string['restorefile_errorcopyingfile'] = 'There was an error during the copy of the file {$a} to the import folder.';
$string['restorefile_fileexists'] = 'There file to be copied (<b>{$a}</b>) exists to the import folder.';
$string['restorefile_filenofound'] = 'The specified file was not found (<b>{$a}</b>) in the backup folder.';
$string['restorefile_nofile'] = 'There was no file to copy to the import folder.';
$string['syncusersok'] = 'The users in the {$a} file have been created correctly.';
$string['username'] = 'SFTP username';
