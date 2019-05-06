<?php
/*
 * Site routes
 * 
 */
$base = array(
    'site/default/index' => array(
        'path' => 'site/default/index', //module controller action
        'params' => ['id', 'value'], //expected parameters
        'methods' => ['before', 'after'], //callable to be called before or after action
        'verb' => ['get'], //how the page can be accessed
        'response' => ['json', 'html'], //response format json,html
        'auth' => ['guest', 'loggedin', 'admin']
    ),
    'admin' => array(
        'path'=>'year'
    )
);

$replacements = require APP_PATH . DS . 'Config' . DS .'routes.php' ;

return array_replace_recursive($base, $replacements);


