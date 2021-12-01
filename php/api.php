<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);

date_default_timezone_set('PRC');

require_once BASE_PATH . '/app/core/controller.class.php';

/**
 * token
 */
$GlobalToken = isset($_SERVER['HTTP_TOKEN']) ? $_SERVER['HTTP_TOKEN'] : "";
define('GlobalToken', $GlobalToken);

/**
 * controller
 */
$controller_perifx = isset($_GET['c']) ? $_GET['c'] : ""; //controller prefix
$controller_name = $controller_perifx . '.class'; //controller name
$controller_path = BASE_PATH . '/app/controller/' . $controller_name . '.php'; //controller path

/**
 * action
 */
$GlobalAction = isset($_GET['a']) ? $_GET['a'] : "";
define('GlobalAction', $GlobalAction);


/**
 * interface type
 * mini-program or web system
 */
$type = isset($_GET['t']) ? $_GET['t'] : "";
define('GlobalType', $type);

/**
 * Request Payload
 * Content-Type: application/json
 */
$requestPayload = file_get_contents("php://input");
$requestPayload = !empty($requestPayload) ? json_decode($requestPayload, true) : array();

/**
 * Check whether the controller and method exist and instantiate
 */
if (file_exists($controller_path)) {
    $controller = new $controller_perifx();
    if (is_callable(array($controller, $GlobalAction))) {
        //check token
        $obj = new Controller();
        if (!$obj->prehandler()) {
            echo json_encode(array(
                "status" => "401",
                "message" => "invalid token"
            ));
            return;
        }
        //work
        echo json_encode($controller->$GlobalAction($requestPayload));
    }
}
