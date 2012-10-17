<?php
/*
The MIT License

Copyright (c) 2011 Eric Parsons

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
namespace tatt;
if (!defined('IN_TATT')) {
    exit;
}

/*
 * This is a DB class for connecting to the database and performing queries.
 * Mostly a wrapper for the mysqli_ set of php functions.
 *
 * To configure DB connections, please see dbconfig.php in v4 baseIncludePath.
 *
 */

/* backslash here since mysql is in global namespace */
class Db extends \mysqli {

    private $db_config;                //Array containing info needed to connect to db
    private $last_query_time = NULL;    //Last query's execution time
    private $longest_query;            //Holds the SQL string of the longest query to execute
    private $longest_query_time = 0;    //Time recorded for longest query to execute
    private $queries;                 //Array for storing query info if logging is enabled.
    private $query_backtracing = FALSE;//If query logging is also enabled, will provide the line and location of the executed query.
    private $query_count = 0;          //Stores the number of queries executed since the object was created
    private $query_logged_count = 0;    //Stores the number of queries executed while logging is enabled
    private $query_logging = FALSE;    //Controls whether or not to log all queries executed by query method
    private $shortest_query_time = NULL;
    private $total_query_time;          //Total time of all queries executed


    /*
     * Constructor takes an array in following format
     * array('host' => 'localhost', 'user' => 'theuser', 'pass' => 'thepass', 'db' => 'thedb')
     */
    public function __construct($db_config = NULL, $persistent=FALSE) {
        if ($db_config == NULL) {
            exit('No DB config specified in DB class construct!');
        }

        $this->db_config = $db_config;
        $hostname = $this->db_config['host'];
        $username = $this->db_config['user'];
        $password = $this->db_config['pass'];
        $database = $this->db_config['db'];

        //MySQLi does not support persistent connections prior to php v5.3
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $this->persistent = $persistent;
        }

        //Prepend p: to hostname if establishing a persistant connection
        if ($this->persistent) {
           $hostname = 'p:' . $hostname;
        }
        parent::__construct($hostname, $username, $password, $database);
        if(mysqli_connect_error()){ 
            throw new ConnectException(mysqli_connect_error(), mysqli_connect_errno()); 
        }

        /* or die('MySQL was unable to connect to database.  MySQL Error (#' . mysqli_connect_errno() . '): ' . mysqli_connect_error()); */
    }

    /*
     * Like num_rows method except you should use this if you don't need to do
     * anything with a result set.  Returns an integer.
     */
    public function count($query) {
        $query = preg_replace("/^SELECT(.*?)FROM/si","SELECT COUNT(*) as thecount FROM",trim($query),1);
        $result = $this->query($query);
        $count = $result->fetch_object()->thecount;
        $result->close();
        return $count;
    }



    /*
     * Enable/Disable logging of queries. Disabled by default.
     */
    public function enable_query_logging($bool) {
        if (is_bool($bool)) {
            $this->query_logging = $bool;
        }
    }

    /*
     * Enable/Disable logging of query backtracing. Disabled by default and
     * does nothing unless Querylogging is also enabled.
     */
    public function enable_query_backtracing($bool) {
        if (is_bool($bool)) {
            $this->query_backtracing = $bool;
        }
    }

    /*
     * Will sanitize an SQL statement to prevent SQL injection attacks.  Always
     * run this on user generated strings before executing a query.  Returns
     * a sanitized string.
     */
    public function escape_string($string) {
        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string); //strip slashes if magic_quotes is enabled.
        }
        return parent::escape_string($string);
    }

    /*
     * Returns the execution time of the last query in milliseconds
     */
    public function get_last_query_time() {
        return $this->last_query_time;
    }

    /*
     * Returns a string containing the query that took the longest time
     * to execute.
     */
    public function get_longest_query() {
        return $this->longest_query;
    }

    /*
     * Returns the seconds the longest query took to execute. Returned
     * as a float.
     */
    public function get_longest_query_time() {
        return $this->longest_query_time;
    }

    /*
     * If query logging is enabled, this will return an array containing all
     * queries that have been executed while query logging was enabled as well
     * as the times they took to execute.
     * Format is a multi-dimensional array as follows.
     * i.e. Array(
     *          Array('query' => "Query1 SQL", 'time' => 0.001),
     *          Array('query' => "Query2 SQL", 'time' => 0.031)
     *      )
     */
    public function get_queries() {
        return $this->queries;
    }

    /*
     * Return an integer indicating the number of queries executed since
     * object creation.
     */
    public function get_query_count() {
        return $this->query_count;
    }

    /*
     * Return an integer indicating the number of queries executed while
     * logging is enabled.
     */
    public function get_query_logged_count() {
        return $this->query_logged_count;
    }

    /*
     * Returns the seconds the shortest query took to execute. Returned
     * as a float.
     */
    public function get_shortest_query_time() {
        return $this->shortest_query_time;
    }

    /*
     * Returns a float indicating the total runtime of all queries executed
     * since object creation.
     */
    public function get_total_query_time() {
        return $this->total_query_time;
    }

    /*
     * Executes an SQL query and records query statistics.  Returns 
     * a resultset if the query is a SELECT query.
     */
    public function query($query) {
        $this->query_count++;
        $start_time = microtime(TRUE);
        $result = parent::query($query);
        if(mysqli_error($this)){
            throw new \exception(mysqli_error($this), mysqli_errno($this));
        }

        $end_time = microtime(TRUE);
        $run_time = $end_time - $start_time;
        $query_info = NULL;

        //Store info in array
        if ($this->query_logging) {
            $this->query_logged_count++;

            $query_info['query'] = $query;
            $query_info['time'] = $run_time;

            if($this->query_backtracing){
                $backtrace = debug_backtrace();

                //var_dump($backtrace);

                if (isset($backtrace[1]['function']) && $backtrace[1]['function'] == 'count'){
                    $caller = $backtrace[1];
                } else {
                    $caller = $backtrace[0];
                }

                $path = pathinfo($caller['file']);
                $file = '...' . substr($path['dirname'],-20,20) . '/' . $path['basename'];
                $query_info['file'] = $file;
                $query_info['line'] = $caller['line'];
            }

            $this->queries[] = $query_info;
        }

        $this->total_query_time = ($this->total_query_time + $run_time);
        $this->last_query_time = $run_time;

        //Check if this is the slowest query
        if ($run_time > $this->longest_query_time) {
            $this->longest_query_time = $run_time;
            $this->longest_query = $query;
        }

        //Check if this is the slowest query
        if ($run_time < $this->shortest_query_time || $this->shortest_query_time == NULL) {
            $this->shortest_query_time = $run_time;
        }

        return $result;
    }
}
