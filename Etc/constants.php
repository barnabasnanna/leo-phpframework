<?php
/**
 * Application constants
 */
define('DS', DIRECTORY_SEPARATOR);

defined('DOMAIN_NAME') or define('DOMAIN_NAME', $_SERVER['HTTP_HOST']);//set the base domain

defined('BASE_PATH') or define('BASE_PATH', dirname(dirname(dirname(__DIR__))));
defined('APP_PATH') or define('APP_PATH', BASE_PATH . DS . 'app');
defined('VENDOR_PATH') or define('VENDOR_PATH', BASE_PATH . DS . 'vendor');
defined('CORE_PATH') or define('CORE_PATH', VENDOR_PATH . DS . 'Leo');
defined('WEB_ROOT') or define('WEB_ROOT', BASE_PATH. DS . 'www');

//LOGGING
defined('LOG_TYPE_INFO') or define('LOG_TYPE_INFO', 'INFO');
defined('LOG_TYPE_USER_ERROR') or define('LOG_TYPE_USER_ERROR', 'USER_ERROR');
defined('LOG_TYPE_APP_ERROR') or define('LOG_TYPE_APP_ERROR', 'APP_ERROR');
defined('LOG_TYPE_DEBUG') or define('LOG_TYPE_DEBUG', 'DEBUG');
defined('LOG_TYPE_WARN') or define('LOG_TYPE_WARN', 'WARN');
defined('LOG_TYPE_FATAL') or define('LOG_TYPE_FATAL', 'FATAL');

include('functions.php');
