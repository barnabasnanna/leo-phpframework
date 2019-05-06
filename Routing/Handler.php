<?php
namespace app\Core\Routing;
/*
 * Created by Barnabas
 * Version 1.0
 * Date: 15/09/2015
 */

/**
 * Handles all routing request
 *
 * @author Barnabas
 */
class Handler {
    //put your code here
    
    public $routes = null;
    
    public function __construct() {
        if($this->inMaintenanceMode())
        {
            $this->setRoutes();
        }
    }
    
    public function inMaintenanceMode()
    {
        return file_exists(BASE_PATH.DS.'maintenance.php');
    }


    public function setRoutes()
    {
        $routeFilePath = __DIR__.DS.'routes.php';
        $this->routes = require $routeFilePath;
    }
    
    public function getRoutes()
    {
        
    }
    
    public function handleResponse()
    {
        print_r($_GET);
    }
}
