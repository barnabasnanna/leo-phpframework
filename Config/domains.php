<?php
/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 06/12/2018
 * Time: 00:42
 */

//Domain settings indexed by domain
return array(

    'example.com' => array(
        'url' => 'www.example.com',
        'http' => 'http://example.com',
        'fancyUrlEnabled' => true, //for friendly url
        'site_settings' => [
            'title' => 'Page title',
        ],
        'db' => [
            'connections' => [
                'default' => [
                    'host' => 'localhost',
                    'password' => 'password',
                    'username' => 'user',
                    'database' => 'database',
                    'logQueries' => true,
                    'logQueryParams' => true
                ]
            ]
        ],
        'defaultController' => 'home', //domain override
        'theme' => [
            'name' => 'default',
            '_class_' => 'Leo\Theme\Theme'
        ]
    )
);