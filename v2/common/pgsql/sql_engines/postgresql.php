<?php

/**
 * Check for postgresql support
 */
if (    !function_exists('pg_pconnect')
        || !function_exists('pg_connect')
        || !function_exists('pg_query')
        || !function_exists('pg_fetch_assoc')
        || !function_exists('pg_free_result')
        || !function_exists('pg_fetch_row')
        || !function_exists('pg_last_error')
        || !function_exists('pg_escape_string')
        || !function_exists('pg_num_rows')
        || !function_exists('pg_affected_rows')
        || !function_exists('pg_close')) {
    trigger_error('Fatal error: PostgreSQL functions are unavailable.', E_USER_ERROR);
}

require_once __DIR__.'/sql_engine.base.php';

/**
 * Postgres SQL Engine
 *
 * @category   SQL_Engines
 * @package    SQL_Engines
 * @author     Tino Didriksen <tino@didriksen.cc>
 * @copyright  2005 Zowy Media
 */
class SQLEnginePostgres extends SQLEngineBase
{
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
        parent::__construct($username, $password, $socket, $hostname, $port, $database, $persistent);
    }

    /**
     * Destructor
     *
     * @access public
     */
    public function __destruct() {
        parent::__destruct();
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
    public function connect($force=false) {
        $constr = '';
        if (empty($this->_socket)) {
            if (!empty($this->_hostname)) {
                $constr .= " host={$this->_hostname}";
            }
            if (!empty($this->_port)) {
                $constr .= " port={$this->_port}";
            }
        }
        if (!empty($this->_username)) {
            $constr .= " user={$this->_username}";
        }
        if (!empty($this->_password)) {
            $constr .= " password={$this->_password}";
        }
        if (!empty($this->_database)) {
            $constr .= " dbname={$this->_database}";
        }

        $force = ($force === true) ? PGSQL_CONNECT_FORCE_NEW : 0;
        if ($this->_persistent == true) {
            $this->_connection = pg_pconnect($constr, $force);
        }
        else {
            $this->_connection = pg_connect($constr, $force);
        }

        if ($this->_connection === false) {
            return false;
        }

        if (@pg_query($this->_connection, 'ROLLBACK') === false) {
            return false;
        }

        if (function_exists('pg_set_error_verbosity')) {
            pg_set_error_verbosity($this->_connection, PGSQL_ERRORS_VERBOSE);
        }

        // "If libpq is compiled without multibyte encoding support, pg_client_encoding() always returns SQL_ASCII."
        if (pg_set_client_encoding($this->_connection, 'UNICODE') == -1) {
            return false;
        }
        $this->_log['encoding'] = pg_client_encoding($this->_connection);

        return true;
    }

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
    public function query($query, $log='') {
        $start = GetMicroTime();
        $this->_log['N']++;
        $this->_log[$this->_log['N']]['Q'] = $query;

		$autotrans = false;
		if (empty($this->_transaction_depth)) {
			$autotrans = true;
			$this->begin();
		}

        $rez = @pg_query($this->_connection, $query);

        if ($rez === false) {
			$err = pg_last_error($this->_connection);
			$this->_log[$this->_log['N']]['E'] = $err;
			if ($autotrans) {
				$this->rollback();
			}
            trigger_error("SQL Error - See backtrace for more information: $err in query $query", E_USER_WARNING);
        }
		else if ($autotrans) {
			$this->commit();
		}

        $time = round(GetMicroTime()-$start, 4);
        if (!empty($log)) {
            $this->_log[$this->_log['N']]['L'] = $log;
        }
        $this->_log[$this->_log['N']]['T'] = $time;
        $this->_log['T'] += $time;

        return $rez;
    }

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
    public function queriesErrorSafe($queries) {
        $this->begin();

        $results = array();
        if (!is_array($queries)) {
            $res = query($queries);

            if ($res === false) {
                $err = true;
                $this->rollback();
                return false;
            } else {
                return $res;
            }
        } else {
            $x = count($queries);
            for ($i = 0;$i < $x;$i++) {
                $res = $this->query($queries[$i]);

                if ($res === false) {
                    $err = true;
                    $this->rollback();
                    return false;
                } else {
                    $results[] = $res;
                }
            }
        }

        $this->commit();
        return $results;
    }

    /**
     * Fetches the next number from a sequence and increments the sequence.
     *
     * @param string $seq Schema qualified name of the sequence to fetch and bump.
     * @return mixed int id to insert with, or false on error.
     * @access public
     */
    public function nextID($seq) {
        $rez = $this->query("SELECT nextval('{$seq}') as insertid", __FILE__.' : '.__LINE__.' : '.__FUNCTION__);
        if ($rez === false) {
            return false;
        }
        $row = $this->fetchAssoc($rez);
        $this->freeResult($rez);
        return intval($row['insertid']); // as integer
    }

    /**
     * Frees the memory associated with a result set.
     *
     * @param resource $result The result set to be freed.
     * @return bool true on success, false on error.
     * @access public
     */
    public function freeResult($result) {
        return pg_free_result($result);
    }

    /**
     * Returns the number of rows in a result set.
     *
     * @param resource $result The result set to count.
     * @return int number of rows on success, false on error.
     * @access public
     */
    public function numRows($result) {
        return pg_num_rows($result);
    }

    /**
     * Returns the number of rows affected by a query.
     *
     * @param resource $result The result set to count.
     * @return mixed int number of rows on success, false on error.
     * @access public
     */
    public function affectedRows($result) {
        return pg_affected_rows($result);
    }

    /**
     * Fetches a row of a result set as an associative array.
     *
     * @param resource $result The result set to fetch from.
     * @param int $num The numeric index of the row to fetch.
     * @return mixed An associative array on success, false on error.
     * @access public
     */
    public function fetchAssoc($result, $num=0) {
        return pg_fetch_assoc($result, $num);
    }

    /**
     * Fetches a whole result set as an associative array.
     *
     * @param resource $result The result set to fetch from.
     * @return mixed An associative array on success, false on error.
     * @access public
     */
    public function fetchAll($result) {
        return pg_fetch_all($result);
    }

    /**
     * Fetches a row of a result set as a numeric array.
     *
     * @param resource $result The result set to fetch from.
     * @param int $num The numeric index of the row to fetch.
     * @return mixed A numeric array on success, false on error.
     * @access public
     */
    public function fetchRow($result, $num=0) {
        return pg_fetch_row($result, $num);
    }

    /**
     * Begins a new transaction block.
     *
     * @return bool false on error.
     * @access public
     */
    public function begin($log='', $con=0) {
		$this->_transaction_depth++;
		$val = $this->query('START TRANSACTION', __FILE__.' : '.__LINE__.' : '.__FUNCTION__);
        return $val;
    }

    /**
     * Ends and commits a transaction block.
     *
     * @return bool false on error.
     * @access public
     */
    public function commit() {
		$val = $this->query('COMMIT', __FILE__.' : '.__LINE__.' : '.__FUNCTION__);
		$this->_transaction_depth--;
        return $val;
    }

    /**
     * Ends and rolls back a transaction block.
     *
     * @return bool false on error.
     * @access public
     */
    public function rollback() {
		$val = $this->query('ROLLBACK', __FILE__.' : '.__LINE__.' : '.__FUNCTION__);
		$this->_transaction_depth--;
        return $val;
    }

    /**
     * Partially rolls back a transaction block.
     *
     * @param string $to The name of the savepoint to roll back to.
     * @return bool false on error.
     * @access public
     */
    public function rollbackToSavepoint($to) {
        return $this->query('ROLLBACK TO SAVEPOINT '.$this->escapeString($to), __FILE__.' : '.__LINE__.' : '.__FUNCTION__);
    }

    /**
     * Sets a savepoint for a later partial rollback.
     *
     * @param string $name The name of the savepoint.
     * @return bool false on error.
     * @access public
     */
    public function savepoint($name) {
        return $this->query('SAVEPOINT '.$this->escapeString($name), __FILE__.' : '.__LINE__.' : '.__FUNCTION__);
    }

    /**
     * Escapes special characters in a string to make it safe to use in a query.
     *
     * @param string $str The string to make safe.
     * @return mixed An escaped string on success, unknown on error.
     * @access public
     */
    public function escapeString($str) {
        $str = pg_escape_string($str);
        return $str;
    }

    /**
     * Closes a connection to the SQL server.
     *
     * @return bool true on success, false on error.
     * @access public
     */
    public function disconnect() {
        if ($this->_persistent != true) {
            return pg_close($this->_connection);
        }
        return true;
    }
}

// register_shutdown_function('SQL_Disconnect');
