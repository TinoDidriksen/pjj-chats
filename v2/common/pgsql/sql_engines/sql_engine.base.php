<?php

/**
 * SQLEngine base class
 *
 * This is the base class for all SQL engines.
 * A few of the Escape functions are implemented directly here.
 *
 * @category    SQL_Engines
 * @package     SQL_Engines
 * @author      Tino Didriksen <tino@didriksen.cc>
 * @author      Sune Wettersten <forexs@lazy.dk>
 * @copyright   2005 Zowy Media
 */
abstract class SQLEngineBase
{
    /**
     * Connection socket. Will attempt to use the socket if this is true.
     *
     * @access protected
     */
    protected $_socket;

    /**
     * Hostname to connect to.
     *
     * @access protected
     */
    protected $_hostname;

    /**
     * Port to use.
     *
     * @access protected
     */
    protected $_port;

    /**
     * Username to log in as.
     *
     * @access protected
     */
    protected $_username;

    /**
     * Password to log in with.
     *
     * @access protected
     */
    protected $_password;

    /**
     * Database to connect to.
     *
     * @access protected
     */
    protected $_database;

    /**
     * Whether to attempt a persistent connection.
     *
     * @access protected
     */
    protected $_persistent;

    /**
     * The connection resource.
     *
     * @access protected
     */
    protected $_connection;

    /**
     * The query log.
     *
     * @access protected
     */
    protected $_log;

    /**
     * A counter to keep track of how deeply nested transactions are.
     *
     * @access protected
     */
    protected $_transaction_depth;

    /**
     * Constructor.
     *
     * @param string $username Username to connect as.
     * @param string $password Password to connect with.
     * @param bool $socket Whether to use an Unix socket or not.
     * @param string $hostname Hostname to connect to.
     * @param int $port Port to connect to.
     * @param string $database Database to connect to.
     * @param bool $persistent Whether to reuse a persistent connection by default or not.
     * @access public
     */
    public function __construct($username='', $password='', $socket=true,
    $hostname='', $port=0, $database='', $persistent=true) {
        $this->_username = $username;
        $this->_password = $password;
        $this->_hostname = $hostname;
        $this->_socket = $socket;
        $this->_port = $port;
        $this->_database = $database;
        $this->_persistent = $persistent;
        $this->_connection = null;

        $this->_log = array();
        $this->_log['N'] = 0;
        $this->_log['T'] = 0;
    }

    /**
     * Destructor
     *
     * @access public
     */
    public function __destruct() {
        $this->disconnect();
    }

    /**
     * Opens a connection to the SQL server
     *
     * Opens a connection to the SQL server, checking for connection errors,
     * and performs a ROLLBACK to make sure any old transaction is cleared.
     *
     * @param bool $force Whether to force a new connection.
     * @return bool true on success, false on error
     * @access public
     */
    abstract public function connect($force=false);

    /**
     * Performs a query.
     *
     * Performs a query, logging the time spent running it, any errors, and the optional log message.
     *
     * @param string $query The query to be run.
     * @param string $log (optional) A message to put in the query log.
     * @return mixed A result set on success, false on error.
     * @access public
     */
    abstract public function query($query, $log='');

    /**
     * Performs one or more queries with rollback on error
     *
     * Performs one or more queries and does a rollback if any error occurs.
     * If there is only one query it will return the result, otherwise
     * it will return an array of results, corrosponding to the order of
     * the list of queries. I.e the first query in the query array
     * will have the first result in the result array.
     * Also note that when you pass multiple queries and one of them fail
     * this function will not tell you which, but just return a single false.
     * (however the debug information will be saved as with any other error)
     *
     * @param string $queries One query or an array of query strings
     * @return mixed One result or an array of results
     * @access public
     */
    abstract public function queriesErrorSafe($queries);

    /**
     * Fetches the next number from a sequence and increments the sequence.
     *
     * @param string $seq Schema qualified name of the sequence to fetch and bump.
     * @return mixed int id to insert with, or false on error.
     * @access public
     */
    abstract public function nextID($seq);

    /**
     * Frees the memory associated with a result set.
     *
     * @param resource $result The result set to be freed.
     * @return bool true on success, false on error.
     * @access public
     */
    abstract public function freeResult($result);

    /**
     * Returns the number of rows in a result set.
     *
     * @param resource $result The result set to count.
     * @return int number of rows on success, false on error.
     * @access public
     */
    abstract public function numRows($result);

    /**
     * Returns the number of rows affected by a query.
     *
     * @param resource $result The result set to count.
     * @return mixed int number of rows on success, false on error.
     * @access public
     */
    abstract public function affectedRows($result);

    /**
     * Fetches a row of a result set as an associative array.
     *
     * @param resource $result The result set to fetch from.
     * @param int $num The numeric index of the row to fetch.
     * @return mixed An associative array on success, false on error.
     * @access public
     */
    abstract public function fetchAssoc($result, $num=0);

    /**
     * Fetches a whole result set as an associative array.
     *
     * @param resource $result The result set to fetch from.
     * @return mixed An associative array on success, false on error.
     * @access public
     */
    abstract public function fetchAll($result);

    /**
     * Fetches a row of a result set as a numeric array.
     *
     * @param resource $result The result set to fetch from.
     * @param int $num The numeric index of the row to fetch.
     * @return mixed A numeric array on success, false on error.
     * @access public
     */
    abstract public function fetchRow($result, $num=0);

    /**
     * Begins a new transaction block.
     *
     * @return bool false on error.
     * @access public
     */
    abstract public function begin($log='', $con=0);

    /**
     * Ends and commits a transaction block.
     *
     * @return bool false on error.
     * @access public
     */
    abstract public function commit();

    /**
     * Ends and rolls back a transaction block.
     *
     * @return bool false on error.
     * @access public
     */
    abstract public function rollback();

    /**
     * Partially rolls back a transaction block.
     *
     * @param string $to The name of the savepoint to roll back to.
     * @return bool false on error.
     * @access public
     */
    abstract public function rollbackToSavepoint($to);

    /**
     * Sets a savepoint for a later partial rollback.
     *
     * @param string $name The name of the savepoint.
     * @return bool false on error.
     * @access public
     */
    abstract public function savepoint($name);

    /**
     * Escapes special characters in a string to make it safe to use in a query.
     *
     * @param string $str The string to make safe.
     * @return mixed An escaped string on success, unknown on error.
     * @access public
     */
    abstract public function escapeString($str);

    /**
     * Quotes and escapes special characters in a string to make it safe to use in a query.
     *
     * @param string $str The string to make safe.
     * @return mixed An escaped and single-quoted string or the string NULL on success, unknown on error.
     * @access public
     */
    public function escapeOrNullString($str) {
        if (empty($str) || strcasecmp($str, 'NULL') == 0) {
            return 'NULL';
        }
        $str = "'".$this->escapeString($str)."'";
        return $str;
    }

    /**
     * Sanitizes an integer for use in a query.
     *
     * @param mixed $str The input to convert to an integer.
     * @return mixed An integer or the string NULL on success, unknown on error.
     * @access public
     */
    public function escapeOrNullInt($str) {
        if (empty($str) || strcasecmp($str, 'NULL') == 0) {
            return 'NULL';
        }
        return intval($str);
    }

    /**
     * Sanitizes a floating point number for use in a query.
     *
     * @param mixed $str The input to convert to a float.
     * @return mixed A float or the string NULL on success, unknown on error.
     * @access public
     */
    public function escapeOrNullFloat($str) {
        if (empty($str) || strcasecmp($str, 'NULL') == 0) {
            return 'NULL';
        }
        return floatval($str);
    }

    /**
     * Single-quotes a string if it is not empty.
     *
     * @param string $str The string to quote.
     * @return mixed A single-quoted string or the string NULL on success, unknown on error.
     * @access public
     */
    public function quoteOrNullString($str) {
        if (empty($str)) {
            return 'NULL';
        }
        return "'".$str."'";
    }

    /**
     * Returns the query log.
     *
     * @return array The query log.
     * @access public
     */
    public function getLog() {
        return $this->_log;
    }

    /**
     * Destroys the log.
     *
     * @access public
     */
    public function wipeLog() {
		unset($this->_log);
        $this->_log = array();
    }

    /**
     * Closes a connection to the SQL server.
     *
     * @return bool true on success, false on error.
     * @access public
     */
    abstract public function disconnect();
}
