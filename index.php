<?php 
	error_reporting(0);
define('CHECK_INCLUDED', true);

require_once 'include/conf.php';
require_once 'include/functions.php';
require 'include/libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

/**
 * validate-app
 * url - /validate-app
 * method - POST
 * params - app_id
 */

$app->post('/validate-app', function() use ($app) {
	// define response array 
	$response = array();


	//add your class, if required
	require_once dirname(__FILE__) . '/include/class/class_validate_app.php';
	$validate_app = new Validate_app();
	$app_id=$app->request()->post('app_id');
	$imei=$app->request()->post('imei');
	$validate = $validate_app->validate_app($app_id,$imei);
	
	if($validate){
	//  success
			$response["e"] = 0;
		
	} else {

	//  error occurred
		$response["e"] = 1;
		
		
	}
	ReturnResponse(200, $response);
});

$app->post('/vehicle-loc-logs', function() use ($app) {
	$app_key=$app->request()->post('app_id');
	$lat=$app->request()->post('lt');
	$lng=$app->request()->post('lg');
	$td=$app->request()->post('td');
	//$response['res']=$app_key.' '.$lat.' '.$lng.' '.$td;
	//add your class, if required
	require_once dirname(__FILE__) . '/include/class/class_vehicle_location_log.php';
	require_once dirname(__FILE__) . '/include/class/class_notifications.php';
	require_once dirname(__FILE__) . '/include/class/class_trip.php';
	$VehicleLocLog = new VehicleLocationLog();
	$Notifications = new Notifications();
	$Trip = new Trip();	
	

	if($td==LOG_LOCATION){
		$result=$VehicleLocLog->logLocation($app_key,$lat,$lng,$id='-1');
	}else if($td==LOG_LOCATION_AND_TRIP_DETAILS){
		//add your class, if required
		require_once dirname(__FILE__) . '/include/class/class_driver.php';
		$Driver = new Driver();
		
		
		$trip_from_lat						=	$app->request()->post('lts');
		$trip_from_lng						=	$app->request()->post('lgs');
		$trip_to_lat						=	$app->request()->post('lte');
		$trip_to_lng						=	$app->request()->post('lge');
		$dataArray['trip_start_date_time']	=	$app->request()->post('srt');
		$dataArray['trip_end_date_time']	=	$app->request()->post('end');
		$dataArray['trip_status_id']		=	TRIP_STATUS_COMPLETED;
		$id									=	$app->request()->post('tid');
		$driver_status						=	DRIVER_STATUS_ACTIVE;

		$Trip->update($dataArray,$id);	
		$Driver->changeStatus($app_id,$driver_status);		
		
		$VehicleLocLog->logLocation($app_key,$trip_from_lat,$trip_from_lng,$id);
		$VehicleLocLog->logLocation($app_key,$trip_to_lat,$trip_to_lng,$id);
	}

	$newtrips			=	$Notifications->tripNotifications($app_key); 
	$canceledtrips		=	$Notifications->tripCancelNotifications($app_key); 
	$updatedtrips		=	$Notifications->tripUpdateNotifications($app_key); 
	
	$td_for_array=1;

	
	if($canceledtrips!=false && count($canceledtrips)>=1){

		$td_for_array=$td_for_array*CANCEL_TRIP;

	}

	if($updatedtrips!=false && count($updatedtrips)>=1){

		$td_for_array=$td_for_array*UPDATE_FUTURE_TRIP;

	}


	
	if($newtrips!=false){
		if($newtrips['trip_id'] > gINVALID){
			$trips=$Trip->getDetails($newtrips['trip_id']);
			if($trips!=false){
			if($trips['trip_type_id']==INSTANT_TRIP){
			$td_for_array=$td_for_array*NEW_INSTANT_TRIP;
			$response['td']=$td_for_array;
			$response['nct']=array('fr'=>$trips['trip_from'],'nid'=>$newtrips['id'],'sec'=>strtotime($trips['pick_up_date'].' '.$trips['pick_up_time']),'tid'=>$trips['id'],'to'=>$trips['trip_to']);
			
			}if($trips['trip_type_id']==FUTURE_TRIP){
			$td_for_array=$td_for_array*NEW_FUTURE_TRIP;
			$response['td']=$td_for_array;
			$response['nft']=array('fr'=>$trips['trip_from'],'nid'=>$newtrips['id'],'sec'=>strtotime($trips['pick_up_date'].' '.$trips['pick_up_time']),'tid'=>$trips['id'],'to'=>$trips['trip_to']);
			
					
			}
			}
		}
	}else{
	
		$td_for_array=$td_for_array*NO_NEW_TRIP;
		$response['td']=$td_for_array;

	} 

	if($canceledtrips!=false){
		$response['clt']=$canceledtrips;
	}
	
	if($updatedtrips!=false){
		for($updated_trips_index=0;$updated_trips_index<count($updatedtrips);$updated_trips_index++){
			$trips=$Trip->getDetails($updatedtrips[$updated_trips_index]);	
				if($trips!=false){
				$trips_updated[$updated_trips_index]=array('fr'=>$trips['trip_from'],'sec'=>strtotime($trips['pick_up_date'].' '.$trips['pick_up_time']),'tid'=>$trips['id'],'to'=>$trips['trip_to']);
				}
			
			}
			$response['upt']=$trips_updated;
		}
	
	ReturnResponse(200, $response);
});

$app->post('/reset', function() use ($app) {
	$response['action']=$app->request()->post('name');
	ReturnResponse(200, $response);
});

$app->post('/user-responds', function() use ($app) {
	$app_key=$app->request()->post('app_id');
	$trip_id=$app->request()->post('tid');
	$notification_id=$app->request()->post('nid');
	$ac=$app->request()->post('ac');
	//add your class, if required
	require_once dirname(__FILE__) . '/include/class/class_driver.php';
	$Driver = new Driver();
	require_once dirname(__FILE__) . '/include/class/class_notifications.php';
	require_once dirname(__FILE__) . '/include/class/class_trip.php';
	$Notifications = new Notifications();
	$Trip = new Trip();	
	if($ac==TRIP_NOTIFICATION_REJECTED){
		$data=array('notification_status_id'=>NOTIFICATION_STATUS_RESPONDED,'notification_view_status_id'=>NOTIFICATION_VIEWED_STATUS);
		$Notifications->updateNotifications($data,$notification_id);

	}else if($ac==TRIP_NOTIFICATION_ACCEPTED){
		$data=array('notification_status_id'=>NOTIFICATION_STATUS_RESPONDED,'notification_view_status_id'=>NOTIFICATION_VIEWED_STATUS);
		$Notifications->updateNotifications($data,$notification_id);
		$trips=$Trip->getDetails($trip_id);
		if($trips['driver_id']==gINVALID){
			$driver_id=$Driver->getDriver($app_key);
			$dataArray=array('driver_id'=>$driver_id);
			$res=$Trip->update($dataArray,$id);
			if($res==true){
				
			}	
		}

	}else if($ac==TRIP_NOTIFICATION_TIME_OUT){

	}
	ReturnResponse(200, $response);
});





function ReturnResponse($http_response, $response) {
	//return response : json
    $app = \Slim\Slim::getInstance();
    $app->status($http_response);
    $app->contentType('application/json');
    echo json_encode($response);
}

$app->run();
?>
