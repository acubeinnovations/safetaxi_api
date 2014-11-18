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

	//add your class, if required
	require_once dirname(__FILE__) . '/include/class/class_vehicle_location_log.php';
	$VehicleLocLog = new VehicleLocationLog();

	if($td==LOG_LOCATION){
		$result=$VehicleLocLog->logLocation($app_key,$lat,$lng,$id='-1');
	}else if($td==LOG_LOCATION_AND_TRIP_DETAILS){
		//add your class, if required
		require_once dirname(__FILE__) . '/include/class/class_trip.php';
		require_once dirname(__FILE__) . '/include/class/class_driver.php';
		$Driver = new Driver();
		$Trip = new Trip();

		$trip_from_lat						=	$app->request()->post('lts');
		$trip_from_lng						=	$app->request()->post('lgs');
		$trip_to_lat						=	$app->request()->post('lte');
		$trip_to_lng						=	$app->request()->post('lge');
		$dataArray['trip_start_date_time']	=	$app->request()->post('srt');
		$dataArray['trip_end_date_time']	=	$app->request()->post('end');
		$dataArray['trip_status_id']		=	TRIP_STATUS_COMPLETED;
		$id									=	$app->request()->post('tid');
		$driver_status						=	DRIVER_STATUS_ACTIVE;

		$Trip->finish($dataArray,$id);	
		$Driver->changeStatus($app_id,$driver_status);		
		
		$VehicleLocLog->logLocation($app_key,$trip_from_lat,$trip_from_lng,$id);
		$VehicleLocLog->logLocation($app_key,$trip_to_lat,$trip_to_lng,$id);
	}

	

	
	ReturnResponse(200, $response);
});

$app->post('/reset', function() use ($app) {
	$response['action']=$app->request()->post('name');
	ReturnResponse(200, $response);
});

$app->post('/trips', function() use ($app) {
	$response['action']=$app->request()->post('name');
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
