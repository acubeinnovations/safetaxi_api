<?php 
	//error_reporting(0);
define('CHECK_INCLUDED', true);

require_once 'include/conf.php';
require_once 'include/functions.php';
require 'include/libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

/**
 * validate-app
 * url - /validate-app
 * method - get
 * params - app_id,imei
 */
$app->get('/', function() use ($app) {
$response["e"] = ERROR;
ReturnResponse(200, $response);
});


$app->get('/validate-app', function() use ($app) {
	// define response array 
	$response = array();
	//add your class, if required
	require_once dirname(__FILE__) . '/include/class/class_validate_app.php';
	$validate_app = new Validate_app();
	$app_id=$app->request()->get('app_id');
	$imei=$app->request()->get('imei');
	$validate = $validate_app->validate_app($app_id,$imei);
	
	if($validate){
	//  success
			$response["e"] = NO_ERROR;
		
	} else {

	//  error occurred
		$response["e"] = ERROR;
		
		
	}
	ReturnResponse(200, $response);
});

/**
 * log locations and fetch notifications
 * url - /vehicle-loc-logs
 * method - get
 * params - app_id,lt,lg,td,lts,lgs,lte,lge,dt,srt,end,tid
 */

$app->get('/vehicle-loc-logs', function() use ($app) {
	$app_key=$app->request()->get('app_id');
	$lat=$app->request()->get('lt');
	$lng=$app->request()->get('lg');
	$td=$app->request()->get('td');
	//$response['res']=$app_key.' '.$lat.' '.$lng.' '.$td;
	//add your class, if required
	require_once dirname(__FILE__) . '/include/class/class_vehicle_location_log.php';
	require_once dirname(__FILE__) . '/include/class/class_notifications.php';
	require_once dirname(__FILE__) . '/include/class/class_trip.php';
	require_once dirname(__FILE__) . '/include/class/class_driver.php';
	$Driver = new Driver();
	$VehicleLocLog = new VehicleLocationLog();
	$Notifications = new Notifications();
	$Trip = new Trip();	
	$driver_exists=$Driver->getDriver($app_key);
	if($driver_exists!=false){

	if($td==LOG_LOCATION){
		$result=$VehicleLocLog->logLocation($app_key,$lat,$lng,$id='-1');
	}else if($td==LOG_LOCATION_AND_TRIP_DETAILS){
		
		$trip_from_lat							=	$app->request()->get('lts');
		$trip_from_lng							=	$app->request()->get('lgs');
		$trip_to_lat							=	$app->request()->get('lte');
		$dataArray['distance_in_km_from_app']	=	$app->request()->get('dt');
		$trip_to_lng							=	$app->request()->get('lge');
		$srt									=	$app->request()->get('srt')/1000;
		$end									=	$app->request()->get('end')/1000;
		$dataArray['trip_start_date_time']		=	date('Y-m-d H:i:s',$srt);
		$dataArray['trip_end_date_time']		=	date('Y-m-d H:i:s',$end);
		$dataArray['trip_status_id']			=	TRIP_STATUS_COMPLETED;
		$id										=	$app->request()->get('tid');
		$driver_status							=	DRIVER_STATUS_ACTIVE;

		$Trip->update($dataArray,$id);	
		$Driver->changeStatus($app_key,$driver_status);		
		
		$VehicleLocLog->logLocation($app_key,$trip_from_lat,$trip_from_lng,$id);
		$VehicleLocLog->logLocation($app_key,$trip_to_lat,$trip_to_lng,$id);
		$VehicleLocLog->logLocation($app_key,$lat,$lng,$id='-1');
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
			$tripdatetime							=$trips['pick_up_date'].' '.$trips['pick_up_time'];
			$trip_type_id=checkFutureOrInstantTrip($tripdatetime);
			$dataArray=array('trip_type_id'=>$trip_type_id);
			$res=$Trip->update($dataArray,$newtrips['trip_id']);
			if($trip_type_id==INSTANT_TRIP){
			$td_for_array=$td_for_array*NEW_INSTANT_TRIP;
			$response['td']=$td_for_array;
			$dates=explode('-',$trips['pick_up_date']);
			$time=explode(':',$trips['pick_up_time']);
			$unixtime=mktime($time[0],$time[1],0,$dates[1],$dates[2],$dates[0])*1000;
			$response['nct']=array('fr'=>$trips['trip_from'],'nid'=>$newtrips['id'],'sec'=>$unixtime,'tid'=>$trips['id'],'to'=>$trips['trip_to']);
			
			}else if($trip_type_id==FUTURE_TRIP){
			$td_for_array=$td_for_array*NEW_FUTURE_TRIP;
			$response['td']=$td_for_array;
			$dates=explode('-',$trips['pick_up_date']);
			$time=explode(':',$trips['pick_up_time']);
			$unixtime=mktime($time[0],$time[1],0,$dates[1],$dates[2],$dates[0])*1000;
			$response['nft']=array('fr'=>$trips['trip_from'],'nid'=>$newtrips['id'],'sec'=>$unixtime,'tid'=>$trips['id'],'to'=>$trips['trip_to']);
			
					
			}	
				$data=array('notification_status_id'=>NOTIFICATION_STATUS_NOTIFIED);
				$Notifications->updateNotifications($data,$newtrips['id']);

				$driver_status=DRIVER_STATUS_ENGAGED;
				$Driver->changeStatus($app_key,$driver_status);	
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
				$dates=explode('-',$trips['pick_up_date']);
				$time=explode(':',$trips['pick_up_time']);
				$unixtime=mktime($time[0],$time[1],0,$dates[1],$dates[2],$dates[0])*1000;
				$trips_updated[$updated_trips_index]=array('fr'=>$trips['trip_from'],'sec'=>$unixtime,'tid'=>$trips['id'],'to'=>$trips['trip_to']);
				}
			
			}
			$response['upt']=$trips_updated;
		}
	}else{
		$response['e']=1;
	}
	
	ReturnResponse(200, $response);
});

/**
 * reset driver status to active 
 * url - /reset
 * method - get
 * params - app_id
 */

$app->get('/reset', function() use ($app) {
	$app_key=$app->request()->get('app_id');
	$driver_status=DRIVER_STATUS_ACTIVE;
	//add your class, if required
	require_once dirname(__FILE__) . '/include/class/class_driver.php';
	$Driver = new Driver();
	$res=$Driver->changeStatus($app_key,$driver_status);
	if($res==true){
		$response["e"]=NO_ERROR;
	}else{
		$response["e"]=ERROR;
	}

	ReturnResponse(200, $response);
});

/**
 * display loged locations for testing pupose only 
 * url - /locations
 * method - get
 * params - 
 */

$app->get('/locations', function() use ($app) {

require_once dirname(__FILE__) . '/include/class/class_vehicle_location_log.php';
$VehicleLocLog = new VehicleLocationLog();

$locations=$VehicleLocLog->getLogocationLogs();
echo "<table><tr>
		<td>id</td>
		<td>app key</td>
		<td>lat</td>
		<td>lng</td>
		<td>lng</td>
		</tr>";
for($i=0;$i<count($locations);$i++){
echo "<tr>
		<td>".$locations[$i]['id']."</td>
		<td>".$locations[$i]['app_key']."</td>
		<td>".$locations[$i]['lat']."</td>
		<td>".$locations[$i]['lng']."</td>
		<td>".$locations[$i]["datetime"]."</td></tr>";

}
echo "</table>";
});

/**
 * user responds 
 * url - /user-responds
 * method - get
 * params - app_id,nid,tid,ac
 */

$app->get('/user-responds', function() use ($app) {
	$app_key=$app->request()->get('app_id');
	$trip_id=$app->request()->get('tid');
	$notification_id=$app->request()->get('nid');
	$ac=$app->request()->get('ac');
	//add your class, if required
	require_once dirname(__FILE__) . '/include/class/class_driver.php';
	$Driver = new Driver();
	require_once dirname(__FILE__) . '/include/class/class_notifications.php';
	require_once dirname(__FILE__) . '/include/class/class_trip.php';
	$Notifications = new Notifications();
	$Trip = new Trip();	
	if($ac==TRIP_NOTIFICATION_REJECTED){
		$data=array('notification_status_id'=>NOTIFICATION_STATUS_RESPONDED,'notification_view_status_id'=>NOTIFICATION_VIEWED_STATUS);
		$res=$Notifications->updateNotifications($data,$notification_id);
		if($res==true){
		$response['ac']=TRIP_REJECTED;
		$driver_status=DRIVER_STATUS_ACTIVE;
		$Driver->changeStatus($app_key,$driver_status);

		}else{
			$response['ac']=TRIP_ERROR;
		}

	}else if($ac==TRIP_NOTIFICATION_ACCEPTED){
		$data=array('notification_status_id'=>NOTIFICATION_STATUS_RESPONDED,'notification_view_status_id'=>NOTIFICATION_VIEWED_STATUS);
		$Notifications->updateNotifications($data,$notification_id);
		$trips=$Trip->getDetails($trip_id);
		if($trips['driver_id']==gINVALID){
			$driver_id=$Driver->getDriver($app_key);
			if($driver_id!=false){
				$dataArray=array('driver_id'=>$driver_id['id'],'trip_status_id'=>TRIP_STATUS_ACCEPTED);
				$res=$Trip->update($dataArray,$trip_id);
				if($res==true){
					$trips=$Trip->getDetails($trip_id);
					require_once dirname(__FILE__) . '/include/class/class_customer.php';
					$Customer = new Customer();
					$Customers=$Customer->getUserById($trips['customer_id']);
					$response['ac']=TRIP_AWARDED;
					$response['cn']=$Customers['name'];
					$response['cm']=$Customers['mobile'];
					if($trips['trip_type_id']==FUTURE_TRIP){
						$driver_status=DRIVER_STATUS_ACTIVE;
						$Driver->changeStatus($app_key,$driver_status);
					}
				}else{
					$response['ac']=TRIP_ERROR;
				}		
			}else{
				$response['ac']=TRIP_ERROR;
			}
		}else{
			$response['ac']=TRIP_REGRET;
			$driver_status=DRIVER_STATUS_ACTIVE;
			$Driver->changeStatus($app_key,$driver_status);
		}

	}else if($ac==TRIP_NOTIFICATION_TIME_OUT){
		$data=array('notification_status_id'=>NOTIFICATION_STATUS_EXPIRED,'notification_view_status_id'=>NOTIFICATION_NOT_VIEWED_STATUS);
		$res=$Notifications->updateNotifications($data,$notification_id);
		if($res==true){
			$response['ac']=TRIP_TIME_OUT;
			$driver_status=DRIVER_STATUS_ACTIVE;
			$Driver->changeStatus($app_key,$driver_status);
		}else{
			$response['ac']=TRIP_ERROR;
		}
	}
	ReturnResponse(200, $response);
});


function checkFutureOrInstantTrip($tripdatetime){

		$date1 = date_create(date('Y-m-d H:i:s'));
		$date2 = date_create($tripdatetime);
		$diff= date_diff($date1, $date2);//echo $diff->d.' '. $diff->h.' '.$diff->i;
		if(($diff->d == 0 && $diff->h==0 && $diff->i > 30) || ($diff->d == 0 && $diff->h > 0) || $diff->d > 0) {

		return FUTURE_TRIP;

		}else{

		return INSTANT_TRIP;

		}

	}


function ReturnResponse($http_response, $response) {
	//return response : json
    $app = \Slim\Slim::getInstance();
    $app->status($http_response);
    $app->contentType('application/json');
    echo json_encode($response);
}

$app->run();
?>
