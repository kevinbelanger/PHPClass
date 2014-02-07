<?php

/**
 * A class to provide easy access to common mssql functions and connections
 *
 */
class MsSql
{
	var $conn;
	var $fetch_type = SQLSRV_FETCH_ASSOC;
	//pour que numrows fonctionne
	var $options = array("Scrollable"=>SQLSRV_CURSOR_STATIC);

	/**
	* Class constructor
	* Requires an array of parameters for the host, user, pass, and db name
	*
	* @param array $params
	* @return DB
	*/
	function __construct($params)
	{
		$dbhost = $params[0];
		$dbuser = $params[1];
		$dbpasswd = $params[2];
		$dbname = $params[3];
		$dbcharset = $params[4];
		
		$connectionInfo = array("UID"=>$dbuser, "PWD"=>$dbpasswd, "Database"=>$dbname,"CharacterSet" => $dbcharset);

		$this->conn = sqlsrv_connect($dbhost, $connectionInfo);
		if(!$this->conn)
		{
			$err = sqlsrv_errors();
			echo $err[0]['message'];
			exit;
		}
	}
	
	function __destruct()
	{
		$this->close();
	}
	
	/**
	* You can set the fetch type if you need to. 
	* Default is SQLSRV_FETCH_ASSOC
	*
	* @param unknown_type $type
	*/
	function setFetchType($type)
	{
		$validTypes = array("SQLSRV_FETCH_NUMERIC", "SQLSRV_FETCH_ASSOC", "SQLSRV_FETCH_BOTH");
		if (in_array($type, $validTypes)) {
			$this->fetch_type = $type;
		}
	}
	
	/**
	* Perform a basis mssql_query function
	*
	* @param unknown_type $sql
	* @return resource false on failure. Will return true on update, delete, drop etc rather than resource
	*/
	function query($sql)
	{
		$result = sqlsrv_query($this->conn, $sql, array(), $this->options);	
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
	function fetchArray($sql) {
	
		$result = sqlsrv_query($this->conn, $sql, array(), $this->options);	
		$this->checkErrors($sql,$result);
		$dataset = array();
		while ($row = sqlsrv_fetch_array($result, $this->fetch_type)) {
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
	function fetchRow($sql)
	{
		$result = sqlsrv_query($this->conn, $sql, array(), $this->options);
		$this->checkErrors($sql,$result);
		$row = sqlsrv_fetch_array($result, $this->fetch_type);
		return $row;
	}
	
	/**
	* Returns the number of rows in the result
	*
	* @param resource $result
	* @return integer
	*/
	function numRows($result)
	{
		return sqlsrv_num_rows($result);
	}
	
	/**
	* Returns the last insert id
	*
	* @return integer
	*/
	function insert_id()
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
	function quote_smart($value)
	{
		return str_replace("'", "''", $value);
	}
	/**
	* Method to read string 
	*
	* @param $value
	* @retun string
	*/
	// added by Isabel Francoeur 

	function write_quote_smart($value){
		$value = nl2br($value);
		return $value;
	}
	
	/**
	* Checks to see if something went wrong and will display a mssql error 
	*
	* @param string $sql
	*/
	function checkErrors($sql,$result) {

		$err = sqlsrv_errors();
		if(!$result) {
			echo "Une erreur est survenue dans le SQL suivant:<br><br>";
			echo $sql."<br><br>";
			echo $err[0]['message'];
			debug_print_backtrace();
			exit;
		} else {
			// Since there was no error, we can safely return to main program.
			return;
		}
	}
	
	function close()
	{
		if($this->conn)
		{
			sqlsrv_close($this->conn);
		}
	}
}

?>
