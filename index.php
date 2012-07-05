<?php
require_once("config.php");
require_once("inc/config.class.php");
require_once("inc/controller.class.php");
require_once("inc/db.class.php");
require_once("inc/io.class.php");
require_once("inc/telnet.class.php");


error_reporting(E_ALL);

$config = new Config($config_data);

// call example:
//  ?method=getAccount&name=harpywar

// wrap the whole thing in a try-catch block to catch any wayward exceptions!
try
{
	// get all of the parameters in the GET request
	$params = $_GET;
	
	// get the method and format it correctly so all the letters are not capitalized
	if ( !@array_key_exists('method', $params) )
			throw new Exception("Missing method name");
			
	$method = strtolower($params['method']);
	
	
	// create a new instance of the controller, and pass it the parameters from the request
	$controller = new Controller($config, $params);
	
	// check if the method exists in the controller; if not, throw an exception.
	if( method_exists($controller, $method) === false )
		throw new Exception('Method is invalid.');

	// method result
	$result = $controller->$method();
	
	// if response is binary data
	if ($controller->isBinary = true)
	{
		#header("Content-type: application/octet-stream; ");
		echo $result;
	}
	else
	{
		// execute the method
		$response['success'] = true;
		$response['data'] = $result;
		
		echo json_encode($response);
	}
		
}
catch( Exception $e )
{
	// catch any exceptions and report the problem
	$response = array();
	$response['success'] = false;
	// encode error message because json_encode throw errors if data contains utf-8 value
	$response['errmsg'] = mb_check_encoding( $e->getMessage(), 'UTF-8' ) ? $e->getMessage() : utf8_encode($e->getMessage());
	
	echo json_encode($response);
}


exit();

