<?php

namespace Leo\Routing;

use Leo\Routing\Route;
use Leo\ObjectBase;
use Leo\Leo;
use Leo\Http\Request;

/**
 * Description of Router
 *
 * @author Barnabas
 */
class Router extends ObjectBase
{

    private $routeDetails = array();
    
    private $configured_routes_array = array();
    
    /**
     * @var Route $route
     */
    public $route = null;

    public function __construct(){}

    /**
     * Store all the configured routes of application
     * @return $this
     */
    public function setConfiguredRoutesArray(){
        $this->configured_routes_array = $this->fetchConfiguredRoutes();
        return $this;
    }
    
    /**
     * From the url, calculate the module, controller, action segments of the request url
     * @param Request $request
     * @return Route
     * @throws \Exception if path could not be resolved
     */
    public function resolveRequestToFilePath(Request $request)
    {
        $route_url = $request->getUrl();

        switch ($route_url) {
            case '':
                $route_url = \leo()->getDomainManager()->get('defaultController') ?:
                    Leo::getConfig('defaultController');
                $this->routeDetails = $this->sortRoute($route_url);
                break;
            default:
                $this->routeDetails = $this->sortRoute($route_url);
                break;
        }

        if (empty($this->routeDetails))
        {
            throw new \Exception($route_url . ' could not be resolved.', 404);
        }

        if(TRUE !== ($error_msg = $this->route->resolve($request, $this->routeDetails)))
        {
            throw new \Exception($error_msg);
        }
    }

    /**
     * Returns the route config if found for url. 
     * It first checks if the route is in the route configuration array
     * Use regex if fancy url has been enabled in the domain level or site wide level
     * if not check if it matches convention
     * @param string $route_url
     * @return array
     */
    private function sortRoute($route_url = '')
    {
        $route_array = [];

        //check configured routes array for url path
        if(count($this->configured_routes_array)){
            
            if (isset($this->configured_routes_array[$route_url]))
            {
                $route_array = $this->configured_routes_array[$route_url];
            }
            elseif(\leo()->getDomainManager()->get('fancyUrlEnabled') OR \Leo::getConfig('fancyUrlEnabled'))
            {//TRY REGULAR EXPRESSION
                $route_array = $this->useRegex($route_url);
            }
        }

        //Checks if path meets convention module/controller/action
        if(empty($route_array))
        {
            $route_array = array('path' => $route_url);
        }
        
        return $route_array;
    }
    
    /**
     * Use regex to resolve path. Also set the Request params
     * @param string $route_url
     * @return array Path details
     * @throws \Exception
     */
    private function useRegex($route_url = '')
    {
        foreach($this->configured_routes_array as $key=>$routeDetails)
        {
            $regexPath = \str_replace(['[w]','[d]'],['(\w+)','(\d+)'], $key);
            
            if(preg_match('#^'.$regexPath.'$#i', $route_url, $matches))
            {
                if(isset($routeDetails['regexParams']) && is_array($routeDetails['regexParams']))
                {
                    if(count($routeDetails['regexParams']) != count($matches)-1 ){
                        throw new \Exception('Insufficient parameters provided');
                    }

                    leo()->getRequest()->setParams(
                        array_combine(
                                $routeDetails['regexParams'],
                                array_slice($matches,1)));
                }
                
                return $routeDetails;
            }

            
        }

        return [];
        
    }

    /**
     * All routes of the application. Merge the application route and the package routes
     * @return array
     */
    protected function fetchConfiguredRoutes()
    {
        $user_routes = Leo::getConfig('routes');
        
        $packages_routes = leo()->getPackageManager()->getPackagesRoutes();

        return array_replace_recursive($packages_routes, $user_routes);
    }
    

    /**
     * Return all routes
     * @return array
     */
    public function getRoutes()
    {
        return $this->configured_routes_array;
    }

    /**
     * Returns Route object
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Is application on maintenance mode
     * @return bool
     * @throws \Exception
     */
    public function inMaintenanceMode()
    {
        $maintenance = Leo::getConfig('maintenance');

        if(is_array($maintenance) && isset($maintenance['isDown']) && $maintenance['isDown']) {
            return true;
        }

        return false;
    }

    public function redirect($href, $replace=true)
    {
        if(!empty($href))
        {
            header('Location: '.$href, $replace);
            exit(0);
        }
        else
        {
            throw new \Exception('Redirect path is an empty string');
        }
    }

}
