<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
	exit();
}

class Trip {

	private $connection;
	public  $error_description;

	function __construct() {
		require_once dirname(__FILE__) . '/class_connection.php';
		$db = New Connection();
		$this->connection = $db->connect();
	}
	
	public function finish($dataArray = array(),$id){
		if($dataArray){
			$i=0;
			$strSQL = "UPDATE  trips SET ";
			foreach($dataArray as $key=>$value){
				$strSQL .= $key."='".mysql_real_escape_string($value);
				if(count($dataArray)>$i){
				$strSQL .="',";
				}else{
				$strSQL .="'";
				}
				$i++;
			}
			$strSQL .=" WHERE id='".$id."'";

			//$strSQL = substr($strSQL,0,-1);

			$rsRES = mysqli_query($this->connection,$strSQL);
			
			if(mysql_affected_rows($this->connection) == 1){
				
				return true;
			}else{
				return false;
			}	
		}else{
			
			return false;
		}
		
	}

	public function booking_details($id)
	{
		
		$strSQL = "SELECT * FROM trips WHERE id = '".mysql_real_escape_string($id)."'";
		$rsRES = mysql_query($strSQL,$this->connection) or die(mysql_error(). $strSQL );
		if ( mysql_num_rows($rsRES) == 1 ){
			return mysql_fetch_assoc($rsRES);
		}else{
			$this->error_description = "Invalid Trip";
			return false;
		}
		
	}

	






}
?>
