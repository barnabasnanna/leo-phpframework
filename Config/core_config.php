<?php
/*
 * Default applicaiton settings. Do not make modifications here
 */

$base = array(
    'defaultController' => 'site',
    'defaultAction' => 'index',
    'actionPrefix' => 'path',//prefix for methods used for handling routing in a controller
    'packages' => [],
    'maintenance' => [
        'isDown' => false,//set to true if site on maintenance mode
        'display' =>'maintenance',//what file or route to display
        'type' => 'route' //this can either be a route of file
    ],
    'domains'=>[],
    'components' => array(
        'formatter'=>[
            '_class_' => 'Leo\Helpers\Formatter\Base',
        ],
        'validator' => [
            '_class_' => 'Leo\Helpers\Validator'
        ],
        'di' => [
            '_class_' => 'Leo\Di\Di'
        ],
        'packageManager' => [
            '_class_' => 'Leo\Packages\PackageManager'
        ],
        'eventManager' => [
            '_class_' => 'Leo\Event\EventManager'
        ],
        'session' => [
            '_class_' => 'Leo\Components\Session'
        ],
        'queryConstructor'=>[
            '_class_' => 'Leo\Helpers\Constructor'
        ],
        'behaviour' => [
            '_class_' => 'Leo\Helpers\Behaviour'
        ],
        'log' => [
            '_class_'=> 'Leo\Log\Writer',

        ],
//        'auth_' => [
//            '_class_' => 'Leo\Rbac\Rbac'
//        ],
        'dbConnect' => [
            '_class_' => 'Leo\Db\Connect',

        ],
        'tableInfo' => [
            '_class_' => 'Leo\Db\TableInfo'
        ],
        'router'=>[
            '_class_'=>'Leo\Routing\Router',
            'route' => [
                '_class_'=>'Leo\Routing\Route'
                ]
        ],
        'request' => [
            '_class_' => 'Leo\Http\Request',
        ],
        'lang' => [
            '_class_' => 'Leo\Intl\Intl',
            'lang'=>'en'
        ],
        'theme' => [
            '_class_' => 'Leo\Theme\Theme'
        ],
        'domainManager' =>[
            '_class_' => 'Leo\Helpers\DomainManager',
        ]
    )
);

/**
 * user application config
 */
$replacements = require APP_PATH . DS . 'Config' . DS . 'config.php';

return array_replace_recursive($base, $replacements);
