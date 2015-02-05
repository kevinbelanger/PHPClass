<?php

class Db_Adapter_Mssql extends Db_Adapter_Abstract
{
	private $_conn = null;
	private $_fetch_type = SQLSRV_FETCH_ASSOC;

	// For numrows function work
	private $_options = array(
		"Scrollable" => SQLSRV_CURSOR_STATIC
	);
	
	protected function connect()
	{
		if(is_resource($this->_conn))
		{
            // connection already exists
            return;
        }
		
		if(!extension_loaded('sqlsrv'))
		{
			throw new Exception('The Sqlsrv extension is required for this adapter but the extension is not loaded!');
		}

		$connectionInfo = array(
			"UID" => $this->_config["username"],
			"PWD" => $this->_config["password"],
			"Database" => $this->_config["database"],
			"CharacterSet" => $this->_config["charset"]
		);

		$this->_conn = sqlsrv_connect($this->_config["host"], $connectionInfo);
		
		if(!$this->_conn)
		{
			$err = sqlsrv_errors();
			echo $err[0]['message'];
			exit;
		}
	}
	
	public function closeConnection()
	{
		if($this->_conn)
		{
			sqlsrv_close($this->_conn);
		}
	}
	
	public function __destruct()
	{
		$this->closeConnection();
	}
	
	/**
	* Perform a basis mssql_query function
	*
	* @param unknown_type $sql
	* @return resource false on failure. Will return true on update, delete, drop etc rather than resource
	*/
	public function query($sql)
	{
		$this->connect();

		$result = sqlsrv_query($this->_conn, $sql, array(), $this->_options);	
		$this->checkErrors($sql, $result);
		return $result;
	}
	
	/**
	* Returns a result set as an array. Used when selecting multiple rows of data
	* When no rows, it returns an empty array NOT false
	* Maybe I should change that :)
	*
	* @param string $sql
	* @return array In the format of fetch_type
	*/
	public function fetchArray($sql)
	{
		$result = $this->query($sql);

		$dataset = array();
		while($row = sqlsrv_fetch_array($result, $this->_fetch_type))
		{
			$dataset[] = $row;
		}
		return $dataset;
	}
	
	/**
	* Returns a result set from a single row as an array
	* Returns false if no rows
	*
	* @param string $sql
	* @return array
	*/
	public function fetchRow($sql)
	{
		$result = $this->query($sql);

		$row = sqlsrv_fetch_array($result, $this->_fetch_type);
		return $row;
	}
	
	/**
	* Returns the number of rows in the result
	*
	* @param resource $result
	* @return integer
	*/
	public function numRows($result)
	{
		return sqlsrv_num_rows($result);
	}
	
	/**
	* Returns the last insert id
	*
	* @return integer
	*/
	public function insert_id()
	{
		$sql = "SELECT @@IDENTITY AS lastID";
		$identity = $this->fetchRow($sql);
		return $identity['lastID'];
	}
	
	/**
	* This method makes the sql statement safe by escaping the values via mssql_real_escape_string()
	* See mssql.com for more info
	*
	* @param mixed $value
	* @return string
	*/
	public function quote_smart($value)
	{
		return str_replace("'", "''", $value);
	}
	
	/**
	* Checks to see if something went wrong and will display a mssql error 
	*
	* @param string $sql
	*/
	protected function checkErrors($sql, $result)
	{
		$err = sqlsrv_errors();
		if(!$result)
		{
			$strError = "Une erreur est survenue dans le SQL suivant:<br /><br />";
			$strError .= $sql."<br /><br />";
			$strError .= $err[0]['message'];
			
			ob_start();
			debug_print_backtrace();
			$backtrace = ob_get_contents();
			ob_end_clean();
			
			$strError .= $backtrace;
			throw new Exception($strError);
		}
		
		return true;
	}
	
	public function beginTransaction()
	{
		return sqlsrv_begin_transaction($this->_conn);
	}

	public function commitTransaction()
	{
		return sqlsrv_commit($this->_conn);
	}

	public function rollbackTransaction()
	{
		return sqlsrv_rollback($this->_conn);
	}
}

?>