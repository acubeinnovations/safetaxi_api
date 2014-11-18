<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
	exit();
}

class Driver{

	public function changeStatus($app_key,$status){
		$strSQL = "UPDATE  drivers SET status_id= '".$status."' WHERE app_key=.'".$app_key."'";
		$rsRES = mysqli_query($this->connection,$strSQL);
			
			if(mysql_affected_rows($this->connection) == 1){
				
				return true;
			}else{
				$this->error_description = "Booking Failed";
				return false;
			}

	}



}
?>
