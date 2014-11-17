<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
	exit();
}

class Validate_app{

	private $connection;
	public  $error_description;

	function __construct() {
		require_once dirname(__FILE__) . '/class_connection.php';
		$db = new Connection();
		$this->connection = $db->connect();
	}

	
	
	public function validate_app($app_id){

		$strSQL = "SELECT id FROM drivers WHERE app_key = ".mysql_real_escape_string($app_id);
		$rsRES = mysqli_query($this->connection,$strSQL);
		if ( mysqli_num_rows($rsRES) == 1 ){

			return true;

		}else{
			return false;
		}	
	}
}
?>
