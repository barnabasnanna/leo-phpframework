<?php

namespace Leo\Routing;

use Exception;
use Leo\Http\Request;
use Leo\ObjectBase;
use RuntimeException;

/**
 * Route class handles all route resolving tasks
 *
 * @author Barnabas Nanna <nannabar@gmail.com>
 * @since 0.1
 * @version 1
 */
class Route extends ObjectBase
{

    /**
      Configuration Route option
     * <pre>
      'site/action' => array(
        'base'=>'Packages/Rbac', //folder where the module, controller action will be searched
        'path' => 'account/site/action', //module/controller action
        'params' => ['id', 'value'], //expected parameters
        'methods' => ['before', 'after'], //callable to be called before or after action
        'verb' => ['get','post'], //how the page can be accessed
        'response' => ['json', 'html'], //response format json,html
        'auth' => ['!guest', 'custom'] //logged in and a custom authentication evaluation level needed
      )
     * </pre>
     *
     */
    private $_routeDetails = [];
    private $controller;
    private $action;
    private $module;
    private $beforeAction;
    private $afterAction;
    private $verb;
    private $responseType = 'html';
    private $responseTypes = ['html','json','xml'];
    private $auth;
    private $controllerFile = '';
    private $moduleFolder = '';
    private $verbs = ['get', 'post', 'ajax'];
    private $default_extension = '.php';
    private $basePath = '';
    

    
    /**
     * how many segments does the route path have
     * /site = 1
     * /site/index = 2
     * account/user/index = 3
     * @var integer
     */
    private $routeSegmentsCount = 0;

    /**
     * Resolve the route details
     * @param Request $request
     * @param array $routeDetails
     * @return boolean was resolving route details successful
     * @throws \Exception
     */
    public function resolve(Request $request, array $routeDetails = [])
    {
        try
        {
            $this->_routeDetails = $routeDetails;
            $this->setControllerAction();
            $this->setBeforeAction();
            $this->setAfterAction();
            $this->setVerb();
            $this->setResponseType();
            $this->setAuth();
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }

        return true;
    }

    private function setVerb()
    {
        $this->verb = [];

        if (!empty($this->_routeDetails['verb']))
        {
            $verb = (array) $this->_routeDetails['verb'];
            /*
             * Retrieve only valid verbs
             */
            $this->verb = array_intersect($verb, $this->getVerbs());
        
            if (!count($this->verb))
            {
                throw new Exception('Page can not be accessed with verbs ' . implode(',', $verb), 403);
            }
        
        }
    }

    public function getVerb()
    {
        return $this->verb;
    }

    public function getAuth()
    {
        return $this->auth;
    }

    private function setAuth()
    {
        if (!empty($this->_routeDetails['auth']))
        {
            $this->auth = (array) $this->_routeDetails['auth'];
        }
    }

    /**
     * How the content to be returned back to the client.
     * can be html, json, xml.
     * Can use the information to format the response
     * @param string $responseType the format the data should be returned from the server
     * @throws Exception
     */
    public function setResponseType($responseType = '')
    {

        if (!empty($this->_routeDetails['response']))
        {
            $type = (array)$this->_routeDetails['response'];
        }
        else
        {
            $type = $responseType ? (array) $responseType : (array) $this->responseType;
        }

        $this->responseType = \array_intersect($type, $this->getResponseTypes());

        if (!count($this->responseType)) {
            throw new \Exception(\implode(',', $type) . ' response type is not supported', 403);
        }

    }

    public function getResponseTypes()
    {
        return $this->responseTypes;
    }

    public function getResponseType()
    {
        return $this->responseType;
    }

    private function setControllerAction()
    {
        $path = $this->_routeDetails['path'];
        $basePath = '';

        //if the route has a base path, path search will be resolved in that folder
        if (!empty($this->_routeDetails['base']))
        {
            $basePath = is_string($this->_routeDetails['base']) ? $this->_routeDetails['base'] : '';
            $this->setBasePath($basePath);
        }
        
        //module/controller/action
        $parts = explode('/', $path);

        $this->routeSegmentsCount = count($parts);
        switch ($this->routeSegmentsCount)
        {
            case 1:
                $this->controller = $parts[0];
                break;
            case 2:
                $this->controller = $parts[0];
                $this->action = $parts[1];
                break;
            case 3:
                $this->module = $parts[0];
                $this->controller = $parts[1];
                $this->action = $parts[2];
                break;
            default:
                throw new Exception('Path could not be resolved', 404);
        }

        $this->resolveRouteSegments($this->controller, $this->module);
    }

    private function resolveRouteSegments($controller = '', $module = null)
    {
        //resolve module
        $this->moduleFolder = $this->findModuleFolder($module);

        //resolve controller
        $this->controllerFile = $this->findController($controller, $module);
    }

    /**
     * Find if a module folder.
     * @param string $module
     * @param bool $throwException
     * @return string module directory path
     * @throws Exception if the module path does not exist
     */
    protected function findModuleFolder($module = null, $throwException = true)
    {
        $moduleFolder = '';
        
        if ($module !== null)
        {
            $moduleFolder = $this->getModulePath($module); //get module directory
            if (!file_exists($moduleFolder))
            {
                if ($throwException)
                {//does not exist
                    throw new RuntimeException($moduleFolder . \lang(' module does not exists.'));
                }
            }
        }

        return $moduleFolder;
    }

    /**
     * Finds the appropriate Controller. Checks in module folder if module is set
     * else it checks in the default controller folder
     * @param string $controllerName
     * @param string|null $module
     * @param bool $throwException
     * @return false|string false if file is not found and throw exception is disabled
     * @throws Exception if file not found
     * @internal param string $controller
     * @internal param string $basePath Base folder where search should be done
     */
    public function findController($controllerName = '', $module = '', $throwException = true)
    {
        $controller = ucfirst($controllerName);

        if ($module && $controller)
        {
            $controllerFile = $this->getModuleController($controller);
        }
        else
        {
            $controllerFile = $this->getControllerPath($controller);
        }

        $controllerFile = $this->attachExt($controllerFile);
        
        $controllerFile_copy = $controllerFile;

        if (!file_exists($controllerFile) && !($controllerFile = $this->stripControllerSuffix($controllerFile)))
        {
            if ($throwException)
            {
               $e_message = 'Controller file ('.$controllerFile_copy . ') not found.';
                throw new \Exception($e_message);
            }
            else
            {
                $controllerFile = false;
            }
        }

        return $controllerFile;
    }
    
    
    
    public function stripControllerSuffix($controllerPath)
    {
        $removeSuffixFromControllerFile = str_replace('Controller.php', '.php', $controllerPath);
        
        if(file_exists($removeSuffixFromControllerFile))
        {
            return $removeSuffixFromControllerFile;
        }
        
        return false;
    }

    /**
     * Attach appropriate file extension to a file
     * @param string $controllerFile
     * @return string
     */
    private function attachExt($controllerFile = '')
    {
        return $controllerFile . $this->getFileExtention();
    }

    /**
     * Finds a controller file in specified module
     * @param string $controller
     * @param boolean $throwException
     * @return string controller file path in module folder
     * @throws Exception if controller is not found
     */
    private function getModuleController($controller, $throwException = true)
    {
        $moduleControllerFile = $this->getModuleFolder() . DS . 
                'Controllers' . DS . $controller .
                'Controller';
        
        return $moduleControllerFile;
    }

    public function getModuleFolder()
    {
        return $this->moduleFolder;
    }

    public function getControllerFile()
    {
        return $this->controllerFile;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getModule()
    {
        return $this->module;
    }

    /**
     * Checks if before is a callable
     * @throws Exception
     */
    private function setBeforeAction()
    {
        if (!empty($this->_routeDetails['methods']) && !empty($this->_routeDetails['methods']['before']))
        {
            $this->beforeAction = $this->_routeDetails['methods']['before'];
        }
    }

    public function getBeforeAction()
    {
        return $this->beforeAction;
    }

    private function setAfterAction()
    {

        if (!empty($this->_routeDetails['methods']) && !empty($this->_routeDetails['methods']['after']))
        {
            if (!is_callable($this->_routeDetails['methods']['after']))
            {
                throw new \Exception('Route after callable is not valid');
            }

            $this->afterAction = $this->_routeDetails['methods']['after'];
        }
    }

    public function getAfterAction()
    {
        return $this->afterAction;
    }

    /**
     * Returns the defualt path of site controllers but if $controller is provided,
     * get controller path in defualt controller directory
     * @param string $controller
     * @return string Controller base path or resolved controller file location
     */
    public function getControllerPath($controller = '')
    {
        $appControllerBaseFolder = APP_PATH . DS . $this->getBasePath(). DS. 'Controllers';
        
        $leoControllerBaseFolder = CORE_PATH . DS . $this->getBasePath(). DS. 'Controllers';
        
        $controllerBaseFolder = \file_exists($appControllerBaseFolder) ? $appControllerBaseFolder : $leoControllerBaseFolder;

        return !$controller ? $controllerBaseFolder : $controllerBaseFolder . DS . $controller . 'Controller';
    }

    /**
     * Get folder where module will be found
     * @param string $module if set, will return the module base path
     * @return string
     * @internal param string $basePath Base folder where module folder exists
     */
    public function getModulePath($module = '')
    {
        $basePath = $this->getBasePath();
        
        $appModuleBaseFolder = APP_PATH . DS . $basePath . DS .'Modules';
        
        $leoModuleBaseFolder = CORE_PATH . DS . $basePath . DS . 'Modules';
        
        $mFolder = file_exists($appModuleBaseFolder) ? $appModuleBaseFolder : $leoModuleBaseFolder;
        
        return !$module ? $mFolder : $mFolder . DS . $module;
        
    }

    public function getFileExtention()
    {
        return $this->default_extension;
    }
    
    
    public function getDefaultExtension()
    {
        return $this->default_extension;
    }

    public function getBasePath($stripSlash = true)
    {
        if($this->basePath)
        {
            return $stripSlash ? $this->basePath : $this->basePath. DS ;
        }
        
    }

    public function getRouteSegmentsCount()
    {
        return $this->routeSegmentsCount;
    }

    public function setDefaultExtension($default_extension)
    {
        $this->default_extension = $default_extension;
        return $this;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    public function setRouteSegmentsCount($routeSegmentsCount)
    {
        $this->routeSegmentsCount = $routeSegmentsCount;
        return $this;
    }
    
    public function getVerbs()
    {
        return $this->verbs;
    }

    /**
     * Get the details of the matched route config
     * @return mixed|null
     */
    public function getRouteDetails(){
        return $this->_routeDetails;
    }

    /**
     * Return the parameters the resolved action needs to function if any
     * @return array
     */
    public function getExpectedParams(){
        return isset($this->_routeDetails['params']) ? $this->_routeDetails['params'] : [];
    }

}
