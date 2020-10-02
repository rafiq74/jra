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
 * This file contains main functions for RCYCI Module
 *
 * @since     Moodle 2.0
 * @package   format_rcyci
 * @copyright Muhammd Rafiq
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
   This file contain all the global functions for RCYCI module
*/

// This is the library for global RCYCI functions 
defined('MOODLE_INTERNAL') || die();

//call this function to initialize external database
function jra_exdb_init()
{
	$dbconfig = new stdClass();
	$dbconfig->sis_dbtype = jra_get_config('sis_dbtype');
	$dbconfig->sis_dbhost = jra_get_config('sis_dbhost');
	$dbconfig->sis_dbuser = jra_get_config('sis_dbuser');
	$dbconfig->sis_dbpassword = jra_get_config('sis_dbpassword');
	$dbconfig->sis_dbname = jra_get_config('sis_dbname');
	
	$exdb = new jra_exdb($dbconfig); //
	$exdb->db_init(); //always use the name db2 as db could be a name used in Moodle
	return $exdb;	
}

/**
 * External database initialization class.
 */
class jra_exdb 
{
	
    var $config; //all the database mapping configurations (check the setting. Must have dbtype, host, database, user, password)
	
	var $db2; //the variable that holds the ADO object for actual connection
	
    /**
     * Constructor.
     */
    function __construct($config) {
        global $CFG;
        require_once($CFG->libdir.'/adodb/adodb.inc.php');
		$this->config = $config;
		
    }


    /**
     * Connect to external database.
     *
     * @return ADOConnection
     * @throws moodle_exception
     */
    function db_init() {
        if ($this->is_configured() === false) {
            throw new moodle_exception('Unable to connect to external database. The database fields are not properly configured');
        }

        // Connect to the external database (forcing new connection).
        $authdb = ADONewConnection($this->config->sis_dbtype);
        if (!empty($this->config->debugauthdb)) {
            $authdb->debug = true;
            ob_start(); //Start output buffer to allow later use of the page headers.
        }
        $authdb->Connect($this->config->sis_dbhost, $this->config->sis_dbuser, jra_decrypt($this->config->sis_dbpassword), $this->config->sis_dbname, true);
        $authdb->SetFetchMode(ADODB_FETCH_ASSOC);
        if (!empty($this->config->setupsql)) {
            $authdb->Execute($this->config->setupsql);
        }
		$this->db2 = $authdb;
    }

	//check if all the basic database fields are configured
	function is_configured()
	{
		if($this->config->sis_dbtype == '' || $this->config->sis_dbhost == '' || $this->config->sis_dbuser == '' || $this->config->sis_dbname == '')
			return false;
		return true;	
	}
	
	function is_error()
	{
		return $this->db2->errorMsg();
	}
	
	
	/////standard database functions ////
	//execute a query
	//recordset is to return in raw record set. This is useful if we need to loop the result separately. It will avoid double looping
	//obj to return the result as object
	//limit is the number of record. 
	//offset is the page (start with 1)
	function execute_query($query, $obj = true, $recordset = false, $offset = -1, $limit = -1)
	{
		global $CFG;
		if($limit != -1 && $offset != -1) //need to implement limit
			$rs = $this->db2->PageExecute($query, $limit, $offset);
		else
			$rs = $this->db2->Execute($query); //normal execute and return a record set
		if (!$rs) 
		{
			if($CFG->production)
				return 'Error executing query due to connection error';
			else	
				return query_error($query);
		}
		return $this->return_value($rs, $obj, $recordset);
	}
	
	function return_value($rs, $obj, $recordset)
	{
		if(!$recordset || $obj) //not record set, or require object means return as array
		{
			$result = array();
			while(!$rs->EOF) 
			{
				$rec = $rs->fields;
				$rec = array_change_key_case ($rec);
				if($obj)			
					$result[] = (object) $rec; //cast it
				else
					$result[] = $rec;
				$rs->MoveNext();
			}
			return $result;
		}
		else
			return $rs;
	}
	
	//obtain a single field
	function get_field($query, $field)
	{
		$rec = $this->execute_query($query);
		if($rec)
		{
			foreach($rec as $row)
				return $row->$field;
		}
		return '';
	}
	
	//obtain a single record
	function get_record($query)
	{
		$rec = $this->execute_query($query);
		if($rec)
		{
			foreach($rec as $row)
				return $row;
		}
		return false;
	}
	
	//obtain all the records
	function get_records($query)
	{
		$rec = $this->execute_query($query);
		return $rec;
	}
	
	//just execute a query with no return value. Usually for insert or updated
	function execute($query)
	{
		$this->db2->Execute($query); //normal execute and return a record set
	}
	
	//for throwing exception when query execution error
	function query_error($query, $proceed = false)
	{
		global $CFG;
		$str = 'Error in executing query : ' . $query;
		return $proceed;
		
		if(!$CFG->production) //not production, show the query detail
			$str = 'Error in executing query : ' . $query;
		else
		{
			if($proceed)
				return true; //if ignore error, don't show the query error
			$str = 'Error in executing query';	
		}
		print_object($str);
		return $proceed; //if proceed is true, then the code will continue
	}
}


/////end of standard functions/////


/////end of standard functions/////
