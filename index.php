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
	$validate = $validate_app->validate_app($app_id);
	
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
	$response['action']=$app->request()->post('name');
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
