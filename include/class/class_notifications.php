<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
	exit();
}

class Notifications {

	private $connection;
	public  $error_description;

	function __construct() {
		require_once dirname(__FILE__) . '/class_connection.php';
		$db = new Connection();
		$this->connection = $db->connect();
	}
	
	

	public function tripNotifications($app_key){
		
		$strSQL = "SELECT * FROM notifications WHERE app_key = '".mysqli_real_escape_string($this->connection,$app_key)."' AND notification_type_id=".NOTIFICATION_TYPE_NEW_TRIP." AND notification_status_id=".gINVALID." AND  notification_view_status_id=".NOTIFICATION_NOT_VIEWED_STATUS." ORDER BY id DESC LIMIT 1";
		$rsRES = mysqli_query($this->connection,$strSQL);
		if ( mysqli_num_rows($rsRES) == 1 ){
			return mysqli_fetch_assoc($rsRES);
		}else{
			
			return false;
		}
		
	}
	public function tripCancelNotifications($app_key){
		
		$strSQL = "SELECT trip_id,id FROM notifications WHERE app_key = '".mysqli_real_escape_string($this->connection,$app_key)."' AND notification_type_id=".NOTIFICATION_TYPE_TRIP_CANCELLED." AND notification_status_id=".gINVALID." AND  notification_view_status_id=".NOTIFICATION_NOT_VIEWED_STATUS." ORDER BY id";
		$rsRES = mysqli_query($this->connection,$strSQL);
		if ( mysqli_num_rows($rsRES) >= 1 ){
			$i=0;
			$data=array('notification_status_id'=>NOTIFICATION_STATUS_RESPONDED,'notification_view_status_id'=>NOTIFICATION_VIEWED_STATUS);
			//return mysqli_fetch_row($rsRES);
			while ($row=mysqli_fetch_row($rsRES))
			{
			$trips[$i]=array('tid'=>$row[0]);	
			$this->updateNotifications($data,$row[1]);
			$i++;
			}
			return $trips;
		}else{
			
			return false;
		}
		
	}
	public function tripUpdateNotifications($app_key){
		
		$strSQL = "SELECT trip_id,id FROM notifications WHERE app_key = '".mysqli_real_escape_string($this->connection,$app_key)."' AND notification_type_id=".NOTIFICATION_TYPE_TRIP_UPDATE." AND notification_status_id=".gINVALID." AND  notification_view_status_id=".NOTIFICATION_NOT_VIEWED_STATUS." ORDER BY id";
		$rsRES = mysqli_query($this->connection,$strSQL);
		if ( mysqli_num_rows($rsRES) >= 1 ){
			$i=0;
			$data=array('notification_status_id'=>NOTIFICATION_STATUS_RESPONDED,'notification_view_status_id'=>NOTIFICATION_VIEWED_STATUS);
			while ($row=mysqli_fetch_row($rsRES))
			{
			$trips[$i]=$row[0];	
			$this->updateNotifications($data,$row[1]);
			$i++;
			}
			return $trips;
		}else{
			
			return false;
		}
		
	}

	public function updateNotifications($dataArray = array(),$id){
		if($dataArray){
			$i=0;
			$strSQL = "UPDATE  notifications SET ";
			foreach($dataArray as $key=>$value){
				$strSQL .= $key."='".mysqli_real_escape_string($this->connection,$value);
				if(count($dataArray)-1>$i){
				$strSQL .="',";
				}else{
				$strSQL .="'";
				}
				$i++;
			}
			$strSQL .=" WHERE id='".$id."'";

			//$strSQL = substr($strSQL,0,-1);

			$rsRES = mysqli_query($this->connection,$strSQL);
			
			if(mysqli_affected_rows($this->connection) == 1){
				
				return true;
			}else{
				return false;
			}	
		}else{
			
			return false;
		}
		
	}
}
