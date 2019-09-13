<?php

/* 
 * Example if a Package configuration array
 */

$routes = require __DIR__.DS.'routes.php';
$event_handlers = require __DIR__.DS.'event_handlers.php';

return array(
    'PackagesManager'=>[
        'name' => 'PackagesManager',
        'base' => 'PackagesManager',//packages folder name in Packages
        'enabled'=> true,
        'event_handlers' => $event_handlers,
        'components'=> [],
        'routes' => $routes,
        'menu' => [
            'admin' => [],
            'frontend' => []
        ]
]
);