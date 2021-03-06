<?php

session_start();

define('ROOT', dirname(dirname(__FILE__)));
require_once (ROOT . '/config/config.php');
global $CONFIG;

// activate/deactivate debug
error_reporting(E_ALL);
$CONFIG->debug && ini_set('display_errors', '1');

// set language
//check config first
$_SESSION['lang'] = isset($CONFIG->lang) ? $CONFIG->lang: null;
$_SESSION['lang'] = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
require_once (ROOT . '/lang/' . $_SESSION['lang'] . '.php');

// autoloader
spl_autoload_register(function ($class) {
    global $CONFIG;
    
    if (file_exists(ROOT . '/app/' . lcfirst($class) . '.class.php')) {
        require_once(ROOT . '/app/' . lcfirst($class) . '.class.php');
    } else if (file_exists(ROOT . '/app/controllers/' . $class . '.php')) {
        require_once(ROOT . '/app/controllers/' . $class . '.php');
    } else if (file_exists(ROOT . '/app/models/' . $class . '.php')) {
        require_once(ROOT . '/app/models/' . $class . '.php');
    } else if (file_exists(ROOT . '/app/helpers/' . $class . '.php')) {
        require_once(ROOT . '/app/helpers/' . $class . '.php');
    } else if (file_exists(ROOT . '/lib/' . $class . '.php')) {
        require_once(ROOT . '/lib/' . $class . '.php');
    } else if (file_exists(ROOT . '/app/views/' . $class . '.php')) {
        require_once(ROOT . '/app/views/' . $class . '.php');
    } else if (file_exists(ROOT . '/app/views/admin/' . $class . '.php')) {
        require_once(ROOT . '/app/views/admin/' . $class . '.php');
    } else if (file_exists(ROOT . '/app/views/user/' . $class . '.php')) {
        require_once(ROOT . '/app/views/user/' . $class . '.php');
    } else { //redirect to 404 page
        RoutingHelper::redirect($CONFIG->wwwroot . '/error/notfound');
    }
});

// set up the database connection
DB::setUp($CONFIG);

// parse the request
$url = isset($_GET['url']) ? $_GET['url'] : null;
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

if ($url == null && $role == null) {
    $controller = "AuthController";
    $action = "asklogin";
    $url_params = array();
} else if ($url == null && isset($role)) {
    $controller = ucfirst($role) . 'Controller';
    $action = 'activity';
    $url_params = array();
} else {
    $url_params = explode('/', $url);
    $controller = ucfirst(array_shift($url_params)); //get the controller
    $controller .= 'Controller';
    $action = $url_params[0] == null ? 'activity' : array_shift($url_params);
}

//check for extra action
isset($url_params[1])
    ? $extra_action = $url_params[1]
    : $extra_action = null;

$extra_params = $_POST;
new $controller($action, array_merge($url_params, $extra_params), $extra_action);
