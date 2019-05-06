<?php

/* 
 * Examples of a Package route
 */

$routes = array(
//    'rbac' => array(
//        'base' => 'Packages/Example_Package', //namespace path of package (optional)
//        'path' => 'Module/auth/index', //module controller action (required)
//        'params' => ['id', 'value'], //expected parameters (optional)
//        'methods' => ['before', 'after'], //callables to be called before or after action (optional)
//        'verb' => ['get'], //how the page can be accessed (optional)
//        'response' => ['json', 'html'], //response format json,html (optional)
//        'auth' => ['guest', 'loggedin', 'admin'] // (optional)
//    ),
    'packagesmanager/install' => array(
        'base' => 'Packages/PackagesManager', //namespace path of package (optional)
        'path' => 'db/install', //controller action
        'auth' => ['admin']
    ),
//    'companies/[w]/user/[d]' => array(
//        'path' => 'site/regex', //controller action
//        'params' => ['company_id','user_id'], //expected parameters
//        'methods' => ['before', 'after'], //callable to be called before or after action
//        'verb' => ['get'], //how the page can be accessed
//        'response' => ['json'], //response format json,html
//        'auth' => ['guest', 'loggedin', 'admin']
//    )
);

return $routes;