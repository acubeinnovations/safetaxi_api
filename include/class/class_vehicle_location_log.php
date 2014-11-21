<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
	exit();
}

class VehicleLocationLog {

	private $connection;
	public  $error_description;

	function __construct() {
		require_once dirname(__FILE__) . '/class_connection.php';
		$db = new Connection();
		$this->connection = $db->connect();
	}

	
	public function logLocation($app_key,$lat,$lng,$trip_id='-1') {

		$strSQL = "INSERT INTO  vehicle_locations_logs (app_key,lat,lng,trip_id) VALUES (";
		$strSQL .="'".$app_key."',";
		$strSQL .="'".$lat."',";
		$strSQL .="'".$lng."',";
		$strSQL .="'".$trip_id."')";
		$result=mysqli_query($this->connection, $strSQL);
		return true;
		
	}
	
	

}
?>
