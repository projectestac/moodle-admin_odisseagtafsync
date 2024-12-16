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
 *  Library to connect to SFTP for tool odisseagtafsync
 *
 * @package    tool_odisseagtafsync
 * @copyright  2021 Toni Ginard <aginard@xtec.cat>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class sftp {

    private $connection; // Resource SSH2 Session
    private $sftp; // Resource SSH2 SFTP
    private $host;
    private $user;
    private $pass;
    private $port;
    private $debug;
    private $debug_path;
    private $logger;
    private $errors = ['connection' => false];

    /**
     * Populate object variables and test the connection
     *
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param int $port
     * @param bool $debug
     * @param string $debug_path
     * @throws Exception
     */
    public function __construct($host = '', $user = '', $pass = '', $port = 22, $debug = false, $debug_path = '') {

        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->port = $port;
        $this->debug = $debug;
        $this->debug_path = $debug_path;
        $this->logger = $this->get_logger($debug, $debug_path);

        $this->add_log('Loaded logger in sftp constructor', 'DEBUG');
        $this->add_log('Created object of class sftp', 'DEBUG');

    }

    /**
     * Open the SSH connection and initialize SFTP subsystem
     *
     * @return bool
     * @throws Exception
     */
    public function connect(): bool {

        $this->add_log('Entering function sftp::connect', 'DEBUG');

        if (!$this->connection = ssh2_connect($this->host, $this->port)) {
            throw new Exception("Could not connect to $this->host on port $this->port.");
        } else {
            $this->add_log('Opened connection to server ' . $this->host . ':' . $this->port, 'DEBUG');
        }

        if (!ssh2_auth_password($this->connection, $this->user, $this->pass)) {
            throw new Exception("Could not authenticate over SSH with user $this->user.");
        } else {
            $this->add_log('SFTP user ' . $this->user . ' successfully authenticated.', 'DEBUG');
        }

        if (!$this->sftp = ssh2_sftp($this->connection)) {
            throw new Exception("Could not initialize SFTP subsystem.");
        } else {
            $this->add_log('SFTP subsystem successfully initialized.', 'DEBUG');
        }

        return true;
    }

    /**
     * Upload file
     *
     * @param $local_file
     * @param $remote_file
     * @throws Exception
     */
    public function set_file($remote_file = '', $local_file = '') {

        $this->add_log('Entering function sftp::set_file', 'DEBUG');

        $data_to_send = file_get_contents($local_file);
        if ($data_to_send === false) {
            throw new Exception("Could not open local file: $local_file.");
        } else {
            $this->add_log('Loaded contents of file ' . $local_file, 'DEBUG');
        }

        $stream = open("ssh2.sftp://$this->sftp$remote_file", 'w');

        if (!$stream) {
            $this->disconnect();
            throw new Exception("Could not open remote file: $remote_file");
        } else {
            $this->add_log('Opened remote file ' . $remote_file, 'DEBUG');
        }

        if (fwrite($stream, $data_to_send) === false) {
            fclose($stream);
            $this->disconnect();
            throw new Exception("Could not write data from file $local_file in $remote_file.");
        } else {
            $this->add_log('Data written to file ' . $remote_file, 'DEBUG');
        }

        fclose($stream);

    }

    /**
     * Download a file from the server. Not using ssh2_scp_recv() because it doesn't work in all servers.
     *
     * @param string $remote_file
     * @param string $local_file
     * @return bool
     * @throws Exception
     */
    public function get_file($remote_file = '', $local_file = ''): bool {

        $this->add_log('Entering function sftp::get_file', 'DEBUG');

        if (empty($remote_file) || empty($local_file)) {
            return false;
        }

        // Check if connection is correct
        if ($this->errors['connection']) {
            return false;
        }

        if (!$file = fopen($local_file, 'w+')) {
            return false;
        } else {
            $this->add_log('Opened local file ' . $local_file, 'DEBUG');
        }

        $data = file_get_contents("ssh2.sftp://$this->sftp$remote_file");

        if (false === $data) {
            $this->disconnect();
            throw new Exception("Could not load remote file: $remote_file");
        } else {
            $this->add_log('Loaded remote file ' . $remote_file, 'DEBUG');
        }

        $bytes = file_put_contents($local_file, $data);

        if (false === $bytes) {
            $this->disconnect();
            throw new Exception("Could not write local file: $local_file");
        } else {
            $this->add_log('Saved local file ' . $local_file, 'DEBUG');
        }

        return true;

    }

    /**
     * Delete file in SFTP server
     *
     * @param string $remote_file
     * @return bool
     * @throws Exception
     */
    public function del_file($remote_file = ''): bool {

        $this->add_log('Entering function sftp::del_file', 'DEBUG');

        if (empty($remote_file)) {
            return false;
        }

        if (!ssh2_sftp_unlink($this->sftp, $remote_file)) {
            $this->disconnect();
            throw new Exception("Could not remove remote file: $remote_file.");
        } else {
            $this->add_log('Deleted remote file ' . $remote_file, 'DEBUG');
        }

        return true;

    }

    /**
     * Check the existence of errors
     *
     * @return bool
     */
    public function is_error(): bool {

        $this->add_log('Entering function sftp::is_error', 'DEBUG');

        return in_array(true, $this->errors);

    }

    /**
     * List the files in the SFTP server
     *
     * @param string $input_path
     * @return array|false|string
     * @throws Exception
     */
    public function get_dir_list($input_path = '') {

        $this->add_log('Entering function sftp::get_dir_list', 'DEBUG');

        if (empty($input_path) || $this->errors['connection']) {
            return false;
        }

        $files = [];

        $this->connect();

        if (!$handle = opendir('ssh2.sftp://' . intval($this->sftp) . $input_path)) {
            $this->disconnect();
            return false;
        } else {
            $this->add_log('Opened remote directory ' . $input_path, 'DEBUG');
        }

        while (false != ($entry = readdir($handle))) {
            if (($entry != '.') && ($entry != '..')) {
                $files[] = $entry;
            }
        }

        closedir($handle);

        $this->add_log('Read remote directory ' . $input_path, 'DEBUG');
        $this->disconnect();

        return (empty($files)) ? false : $files;

    }

    /**
     * Close the SSH connection
     */
    public function disconnect() {

        $this->add_log('Entering function sftp::disconnect', 'DEBUG');

        ssh2_disconnect($this->connection);

    }

    /**
     * Save information in the log
     *
     * @param $str
     * @param string $type
     */
    private function add_log($str, $type = 'INFO') {

        if ($this->logger) {
            $this->logger->add('sftp: ' . $str, $type);
        }

    }

    /**
     * @param false $debug
     * @param string $path
     * @return false|odissea_log4p|null
     */
    private function get_logger($debug = false, $path = '') {

        try {
            return odissea_log4p::instance(true, $path, $debug);
        } catch (Exception $e) {
            debugging('ERROR: Cannot initialize logger, there won\'t be any log.');
            debugging($e->getMessage());
        }

        return false;

    }

}
