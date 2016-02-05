<?php

/**
 * This class manages an ftp connection
 *
 * @author IECISA @mmartinez (version 1.0)
 * @author UPCNET @sarjona (version 1.1)
 * @version 1.1
 */
class ftp4p {

    /**
     * Variables to use
     */
    private $connection, $host, $name, $pass, $permanent; // ftp
    private $logger;                   // log
    private $errors = array('connection' => false);      // errors

    /**
     * Class constructor
     *
     * @param string $host      -> ftp host name
     * @param string $user      -> ftp user name
     * @param string $pass      -> ftp password for the passed user name
     * @param bool   $permanent -> if true ftp is connected during all the session, if false just connect when a transfer is requested
     * @param bool   $debug     -> if true full log is stored, if false just store a resume log
     * @param string $debugpath -> path where to store log file
     */

    function __construct($host = '', $user = '', $pass = '', $permanent = true, $debug = false, $debugpath = null) {

        //set up logger class
        $this->logger = $this->get_logger($debug, $debugpath);

        $this->add_log('Loading class...');

        //set up use variables
        $this->permanent = $permanent;
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        //call to connect to test if values are correct and service is available
        $this->connect();

        //if connection is stablished and permanent var is false close connection
        if ($this->connection && !$this->permanent) {
            $this->close_connection();
        }

        if (in_array(true, $this->errors)) {
            $this->add_log('Class loaded but with errors', 'WARNING');
        } else {
            $this->add_log('Class loaded successfull');
        }
    }

    /**
     * Connect to the ftp server
     *
     * @param string $host -> ftp host name
     * @param string $user -> ftp user name
     * @param string $pass -> ftp password for the passed user name
     * @return bool        -> if true all was successfull, if false some thing fails
     */
    private function connect($host = '', $user = '', $pass = '') {

        $this->add_log('Connecting...', 'DEBUG');

        //reset connection error value
        $this->errors['connection'] = false;

        //check if any arg is empty
        if (empty($this->host) || empty($this->user) || empty($this->pass)) {
            $this->add_log('Connection failed, there is some connect value empty', 'ERROR');
            $this->errors['connection'] = true;
            return false;
        }
        $this->add_log('Connection values: {host:' . $this->host . ', user:' . $this->user . ', pass:' . $this->pass . '}', 'DEBUG');

        //test host
        $url = parse_url($this->host);
        if (!array_key_exists('host', $url)) {
            $url = new StdClass;
            $url->host = $this->host;
            $url->port = 21;
        }
        if (!$this->connection = @ftp_connect($url->host, $url->port)) {
            $this->add_log('Connection failed, host "' . $this->host . '" unavailable', 'ERROR');
            $this->errors['connection'] = true;
            return false;
        }
        $this->add_log('Connected to host', 'DEBUG');

        //test login
        if (!@ftp_login($this->connection, $this->user, $this->pass)) {
            $this->add_log('Connection failed, login values are incorrect', 'ERROR');
            $this->errors['connection'] = true;
            return false;
        } else {
            // Activate passive mode
            @ftp_pasv($this->connection, true);
        }
        $this->add_log('Login done successfully', 'DEBUG');
        $this->add_log('Connected successfully');

        return true;
    }

    /**
     * Close the opened connection
     *
     * @return bool -> if true all was successfull, if false some thing fails
     */
    private function close_connection() {

        $this->add_log('Closing connection...', 'DEBUG');

        //try to close connection
        if (!ftp_close($this->connection)) {
            $this->add_log('Closing connection failed, imposible to close', 'ERROR');
            return false;
        }
        $this->add_log('Connection closed successfully');

        $this->connection = false;
        return true;
    }

    /**
     * Get a list with the fiels of a directory
     *
     * @param  string $dir -> path where get list
     * @return array       -> if all was ok return array, if not return false
     */
    public function get_dir_list($path = '') {

        $this->add_log('Getting path list...', 'DEBUG');

        //check if argument is empty
        if (empty($path)) {
            $this->add_log('Getting path fails, $path parameter is empty', 'ERROR');
            return false;
        }
        $this->add_log('Getting path values: {path:' . $path . '}', 'DEBUG');

        //check if connection is correct
        if ($this->errors['connection']) {
            $this->add_log('Getting file stopped, becouse connection values are incorrect', 'ERROR');
            return false;
        }

        //if connection is not permanetly open it
        if (!$this->permanent) {
            if (!$this->connect()) {
                return false;
            }
        }

        //check if isset the given path
        if (!@ftp_chdir($this->connection, $path)) {
            $this->add_log('Getting path fails, path not exits', 'ERROR');
            return false;
        }

        $this->add_log('Check getting path successfully', 'DEBUG');

        //try to get the files list
        $flist = ftp_nlist($this->connection, $path);
        if ($flist === FALSE) {
            $this->add_log('Getting path fails, ftp nlist return error', 'ERROR');
            return false;
        }

        $this->add_log('Getting path successfully', 'DEBUG');

        //process list for just return files
        $flistcleaned = array();
        foreach ($flist as $f) {
            if ($f != '.' && $f != '..') {
                if (!@ftp_chdir($this->connection, $f)) {
                    $flistcleaned[] = $f;
                }
            }
        }
        $this->add_log('Getting path list cleaned successfully', 'DEBUG');

        //if connection is not permanetly close it
        if (!$this->permanent) {
            $this->close_connection();
        }

        $this->add_log('Getting path successfull. Values: {' . serialize($flistcleaned) . '}');

        return $flistcleaned;
    }

    /**
     * Download a file from the server
     */
    function get_file($remotefile = '', $localfile = '', $returnhandler = true) {

        $this->add_log('Getting file '.$localfile.'...', 'DEBUG');

        //check if argument is empty
        if (empty($remotefile) || empty($localfile)) {
            $this->add_log('Getting file fails, $remotefile parameter is empty', 'ERROR');
            return false;
        }
        $this->add_log('Getting file values: {remotefile:' . $remotefile . ', localfile:' . $localfile . ', returnhandler:' . $returnhandler . '}', 'DEBUG');

        //check if connection is correct
        if ($this->errors['connection']) {
            $this->add_log('Getting file stopped, becouse connection values are incorrect', 'ERROR');
            return false;
        }

        //if connection is not permanetly open it
        if (!$this->permanent) {
            if (!$this->connect()) {
                return false;
            }
        }
        //set local file
        if (!$f = fopen($localfile, "w+")) {
            $this->add_log('Getting file fails, imposible to open local file', 'ERROR');
            return false;
        }
        $this->add_log('Getting file open local file successfull', 'DEBUG');

        //get remote file to local
        if (!ftp_fget($this->connection, $f, $remotefile, FTP_BINARY)) {
            $this->add_log('Getting file fails, imposible to download file fron ftp server', 'ERROR');
            return false;
        }
        $this->add_log('Getting file downloaded successfull');

        //if connection is not permanetly close it
        if (!$this->permanent) {
            $this->close_connection();
        }

        //if returnhandler true return pointer to file else, close file and return localfile value
        if ($returnhandler) {
            return $f;
        } else {
            if (!fclose($f)) {
                $this->add_log('Getting file return error when try to close the pointer to the file', 'WARNING');
            }
            return $localfile;
        }
    }

    /**
     * Upload a file to the server
     */
    function set_file($remotefile = '', $localfile = '', $returnhandler = true) {

        $this->add_log('Setting file...', 'DEBUG');

        //check if argument is empty
        if (empty($remotefile) || empty($localfile)) {
            $this->add_log('Setting file fails, $remotefile parameter is empty', 'ERROR');
            return false;
        }
        $this->add_log('Setting file values: {remotefile:' . $remotefile . ', localfile:' . $localfile . ', returnhandler:' . $returnhandler . '}', 'DEBUG');

        //check if connection is correct
        if ($this->errors['connection']) {
            $this->add_log('Setting file stopped, becouse connection values are incorrect', 'ERROR');
            return false;
        }

        //if connection is not permanetly open it
        if (!$this->permanent) {
            if (!$this->connect()) {
                return false;
            }
        }

        //set local file
        if (!$f = fopen($localfile, "r")) {
            $this->add_log('Setting file fails, imposible to open local file', 'ERROR');
            return false;
        }
        $this->add_log('Setting file open local file successfull', 'DEBUG');

        //get remote file to local
        if (!ftp_fput($this->connection, $remotefile, $f, FTP_BINARY)) {
            $this->add_log('Setting file fails, imposible to upload file', 'ERROR');
            return false;
        }
        $this->add_log('Setting file downloaded successfull');

        //if connection is not permanetly close it
        if (!$this->permanent) {
            $this->close_connection();
        }

        //if returnhandler true return pointer to file else, close file and return localfile value
        if ($returnhandler) {
            return $f;
        } else {
            if (!fclose($f)) {
                $this->add_log('Setting file return error when try to close the pointer to the file', 'WARNING');
            }
            return $localfile;
        }
    }

    /**
     * Delete file form ftp server
     */
    function del_file($remotefile = '') {

        if (empty($remotefile)) {
            return false;
        }

        if (!ftp_delete($this->connection, $remotefile)) {
            $this->add_log('Cannot delete remote file '.$remotefile, 'ERROR');
            return false;
        }
        $this->add_log('Deleted remote file '.$remotefile, 'ERROR');

        return true;
    }

    /**
     * Check if isset the logger class, else denie any log
     *
     * @param bool $debug -> activate debug mode or not
     * @param string $path -> path where to store log file
     * @return bool       -> true if logger could be loaded or false if not
     */
    private function get_logger($debug = false, $path = '') {
        if (!@include_once('log4p.class.php')) {
            return false;
        }
        try {
            return log4p::instance(true, $path, $debug);
        } catch (Exception $e) {
            debugging('ERROR: Cannot initialize apligestlogger, there won\'t be any log.');
            debugging($e->getMessage());
        }
        return false;
    }

    /**
     * Shows a message when php debug is in the highest error_reporting
     */
    private static function debugging($message) {
        $debugdisplay = ini_get('display_errors');
        if (($debugdisplay == '1' || strtolower($debugdisplay) == 'on') && (error_reporting() >= E_ALL | E_STRICT)) {
            if (defined('CLI_SCRIPT') && CLI_SCRIPT) {
                echo "++ $message ++\n";
            } else {
                echo '<div class="notifytiny debuggingmessage" data-rel="debugging">' , $message , '</div>';
            }
        }
    }
    private function add_log ($str, $type = 'INFO*') {
        if ($this->logger) {
            $this->logger->add('ftp4p: '.$str, $type);
        }
    }
    /**
     * Print de log generated by the class
     *
     * @return string -> full string with the log
     */
    public function print_log() {
        if ($this->logger) {
            $this->logger->print_log();
        }
    }
}