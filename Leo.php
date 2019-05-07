<?php

namespace Leo;


use Exception;
use Leo\Components\Session;
use Leo\Controller\BaseController;
use Leo\Db\TableInfo;
use Leo\Di\Di;
use Leo\Exception\MissingConfigException;
use Leo\Helpers\Constructor;
use Leo\Helpers\DomainManager;
use Leo\Http\Request;
use Leo\Interfaces\Logger;
use Leo\Exception\LogException;
use Leo\Log\Writer;
use Leo\Routing\RouteDi;
use Leo\Routing\Router;
use Leo\Theme\Theme;
use Leo\View\View;
use ReflectionMethod;
use stdClass;

/**
 * Entry class
 * @author Barnabas
 */
class Leo extends ObjectBase
{

    /**
     * A repository used to store component classes created from
     * configurable arrays;
     * <p>Components are served from here if already instantiated.</p>
     * @var array
     */
    private static $components = array();
    private static $instance = null;
    private static $config = array();
    private static $module; //resolved module
    private static $controller; //resolved controller
    private static $action; //resolved action
    private static $start_time;
    private static $end_time;

    /**
     * Design pattern the application is using.
     * The router component uses this to located relevant files
     * <br/>
     * Possible value is mvc. hmvc and rest not supportted yet
     * @var string
     */
    public $d_pattern = 'mvc';

    /**
     * Stores the html content to be rendered.
     * @var string
     */
    public static $content;

    const COMPONENTS = 'components';

    /**
     * Application entrance.
     * Loads application config and returns instance of class
     * @return Leo
     * @throws Exception
     */
    public static function init()
    {
        if (is_null(static::$instance)) {
            self::$start_time = microtime(true);
            self::loadConfig();//load site config files
            self::addComponent('di', Di::ini());//DI for loading classes dynamically
            self::log('START APPLICATION', LOG_TYPE_DEBUG);
            if (PHP_SAPI !== 'cli') self::getComponent('session')->start();//start session
            self::$instance = new static();
        }

        return static::$instance;
    }

    public function getRequestStartTime()
    {
        return self::$start_time;
    }

    /**
     * Handle client requests
     * Load Router component class
     * Construct application routes
     * Check if application is in maintenance mode
     * @throws Exception
     */
    public function fetch()
    {
        try {
            /**
             * @var Router $router
             */
            $router = self::getComponent('router');

            $router->setConfiguredRoutesArray();//set the routes

            if ($router->inMaintenanceMode())
            {
                //TODO Maintenace mode handler
                exit('Site down for maintenance. Please visit again soon.');
            }

            $router->resolveRequestToFilePath(self::getComponent('request'));

            $route = $router->getRoute();

            self::$module = $route->getModuleFolder();//store requested module
            self::$controller = $route->getControllerFile();//store requested controller
            self::$action = $route->getAction();//store requested action

            $this->runAction();

            self::$end_time = \microtime(true);
            self::log('END APPLICATION in ' . (1000 * (self::$end_time - self::$start_time)) . 'ms', LOG_TYPE_DEBUG);


        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Return the components segment of app config array.
     * This does not return any changes at runtime
     * @return array
     * @throws Exception
     */
    public function getComponentsConfig()
    {
        return self::getConfig(self::COMPONENTS);
    }

    /**
     * Return components already instantitated by application
     * @return array
     */
    public static function getComponents()
    {
        return self::$components;
    }

    /**
     * Add the instantianted component object to temporary store
     * @param string $name
     * @param object $object
     */
    private static function addComponent($name, $object)
    {
        if (is_object($object)) {
            self::$components[(string)$name] = $object;
        }
    }

    /**
     * Get the configuration array of a component.
     * A configuration array is one with _class_ as a key.
     * <br/>_class_ value is the part to the instatiated class.
     * <p>You can even have properties with configuration arrays as their value and they too will be
     * loaded accordingly.</p>
     * <pre>
     * [
     *      '_class_' => 'classNamespace',
     *      'property_name' => 'property_Value'.
     *      'property_name' => [
     *                              'property_name'=>'property_value',
     *                              '_class_' => 'classNamespace'
     *                          ]
     * ]
     * </pre>
     * Instantiates a component from app config. If already previously loaded,
     * return loaded component object
     * @param string $component_name component you want loaded
     * @param array $properties initialising properties
     * @param bool $throwException
     * @return mixed loaded component
     * @throws LogException
     * @throws Exception
     */
    public static function getComponent($component_name, array $properties = null, $throwException = true)
    {
        if (!is_string($component_name)) {
            throw new \Exception('$component_name must be a string');
        }

        $instantiated_components = self::getComponents();

        if (!isset($instantiated_components[$component_name])) {//if not loaded already
            if ($component_name != 'log') {//to prevent continuous cyclic reference
                self::log('Loading component with name '.$component_name.' and storing in static storage.');
            }

            $component_config= self::getConfig($component_name, self::COMPONENTS);

            if ($component_config) {//component is defined in config, load and store

                self::addComponent(
                    $component_name,
                    self::loadComponent
                    (
                        $component_config, $properties
                    )
                );
            } elseif ($throwException) {
                new MissingConfigException($component_name . ' not found in application config', $component_name);
            } else {
                return false;
            }
        }

        return self::$components[$component_name];
    }

    /**
     * Loads a configurable component. Override any default properties if user properties are provided
     * @param array $component
     * @return object instantiated component
     * @throws Exception
     */
    private static function loadComponent(array $component, array $properties = null)
    {
        $componentArray = \array_replace_recursive($component, (array)$properties);
        return self::loadClass($componentArray);
    }

    public function getController()
    {
        return self::$controller;
    }

    public function getModule()
    {
        return self::$module;
    }

    /**
     * Reloads a component by first flushing it if it already exists in component cache
     * @param string $name component name
     * @param array $properties component name
     * @return object instantiated component class
     * @throws Exception
     */
    public static function reloadComponent($name, array $properties = null)
    {
        if (is_string($name)) {
            self::flushComponents($name);

            return self::getComponent($name, $properties);
        }

    }

    /**
     * Alias for reloadComponent method.
     * <p>Reloads a component by first flushing it if it already exists in component cache</p>
     * @param string $name component name
     * @param array $properties component name
     * @return object instantiated component class
     * @throws Exception
     */
    public static function rc($name, array $properties = null)
    {
        return self::reloadComponent($name, $properties);
    }

    /**
     * Removes a component from component cache. If none specified if empties
     * component cache
     * @param string $name component name you want flushed from component cache
     */
    public static function flushComponents($name = '')
    {
        if ($name) {
            if (isset(self::$components[$name])) {
                unset(self::$components[$name]);
            }
        } else {
            self::$components = array(); //reset component cache
        }
    }

    /**
     * Load the application's config
     */
    private static function loadConfig()
    {
        self::$config = require CORE_PATH . DS . 'Config' . DS . 'core_config.php';
    }

    /**
     * Returns all or part of the application configuration
     * @param string $section indicates which subset of the config to return eg components or routes
     * @return array All or part of application configuration array
     */
    private static function get($section = '')
    {

        if ($section) {
            if (isset(self::$config[$section]) && is_array(self::$config[$section])) {
                return self::$config[$section];
            }
        } else {
            return self::$config;
        }
    }

    /**
     * Used to retrieve information from the application configuration array
     * @param string $name key name of configuration requested
     * @param string $section which section interested in.
     * Can query inner parts of application config.
     * A section must always return an array value e.g components array
     * @param boolean $throwException should an exception be thrown if component name not found
     * @return mixed
     * @throws Exception if not found
     */
    public static function getConfig($name, $section='', $throwException = true)
    {

        if (self::checkConfigExists($name, $section)) {
            return self::get($section)[$name];
        } elseif ($throwException) {
            $message = sprintf('Requested config key [%s] not found in '
                . 'section [%s]', $name, $section);
            throw new MissingConfigException($message);
        }

        return null;

    }

    /**
     * Set the theme of site. If a domain has a it's setting, use that.
     * If a theme name was provided, use that
     * @param string $themeName
     * @throws Exception
     */
    public static function setTheme($themeName = null)
    {

        if (self::checkConfigExists('theme', 'domain')) {//if there exist a domain override use
            $themeManager = self::loadClass(self::getConfig('theme', 'domain'));
            self::addComponent('theme',$themeManager);
        } else {//else use the default theme manager with default
            $themeManager = self::getComponent('theme');
        }

        if (is_string($themeName)) {//set theme name if given
            /**
             * @var $themeManager Theme
             */
            $themeManager->setName($themeName);
        }

    }

    /**
     * Checks if a configuration key exist in the configuration section searched.
     * The configuration section searched is determined by <b>$section</b> and <b>$package</b> values passed
     * @param string $key index name of config array key you want checked
     * @param string $section used to search inner parts of the config array.
     * So to check if a component exist , pass <b>components</b> string and
     * config['components'] will be searched.
     * @return boolean
     */
    public static function checkConfigExists($key = '', $section = '')
    {
        $config = self::get($section);

        if (\is_array($config) && \array_key_exists($key, $config)) {
            return true;
        }

        return false;
    }

    /**
     * @throws LogException
     * @throws \ReflectionException
     * @throws Exception
     */
    private function runAction()
    {

        $class = $this->getControllerClass();

        if (!$class) {
            if ('production' === LEO_RUNNING_MODE) {
                leo()->getRouter()->redirect('/404', true);
            }

            throw new Exception('Controller path not found for ' . $this->getRequest()->getUrl());
        }

        /**
         * @var $controllerClass BaseController
         */
        $controllerClass = new $class;

        $reflectionMethod = new \ReflectionMethod($controllerClass, $this->getAction($controllerClass)->action);

        if ($reflectionMethod->isPublic()) {

            if (is_subclass_of($controllerClass, 'Leo\Controller\BaseController') &&
                $controllerClass->runBeforeAction()) {

                //get request provided params
                $user_params = $this->getRequest()->allParams();

                //get expected parameters in route array
                $route_expected_parameters = $this->getRouter()->getRoute()->getExpectedParams();

                //sort the request parameters to match that of method about to be called
                $method_params = RouteDi::getMethodParams($reflectionMethod, $user_params,$route_expected_parameters);

                //log user request
                self::log_request($reflectionMethod, $method_params);

                //run the action passing parameters
                $reflectionMethod->invokeArgs($controllerClass, $method_params);

                //send the response
                $controllerClass->sendResponse();

                //run any after action methods defined in route
                $controllerClass->runAfterAction();
            }

        }
    }

    /**
     * Return a class with information about the action being run. If none set, returns the defaultAction or
     * index
     * @param BaseController $controller
     * @return stdClass
     */
    public function getAction(BaseController $controller)
    {
        if (!self::$action) {//if one not provided in routing url, get default for controller
            self::$action = $controller->getDefaultAction();
        }

        if (!is_object(self::$action)) {
            $inlineAction = new stdClass();
            $inlineAction->action = $controller->getActionPrefix() . self::$action;
            $inlineAction->id = self::$action;
            self::$action = $inlineAction;
        }

        return self::$action;
    }

    /**
     *
     * @return \Leo\Routing\Router
     * @throws Exception
     */
    public function getRouter()
    {
        return self::getComponent('router');
    }

    private static function getExt()
    {
        return '.php';
    }

    private function getControllerClass()
    {
        $controller = rtrim($this->getController(), self::getExt());
        $subject = str_replace(BASE_PATH . '/', '', str_replace(VENDOR_PATH . '/', '', $controller));
        return str_replace('/', '\\', $subject);
    }


    /**
     *
     * @param ReflectionMethod $reflectionMethod
     * @param array $method_params
     * @throws LogException
     * @throws Exception
     */
    public static function log_request($reflectionMethod, $method_params)
    {
        $data = sprintf('Entry controller %s::%s called with %s', $reflectionMethod->getDeclaringClass()->getName(), $reflectionMethod->getName(), serialize($method_params));
        self::log($data, LOG_TYPE_DEBUG);
    }


    /**
     * Get request component
     * @return Request
     * @throws Exception
     */
    public static function request()
    {
        return self::getComponent('request');
    }

    /**
     * Writes to application logs
     * @param string $data inforamtion you want logged
     * @param string $type Logging type. Default is DEBUG
     * @throws Exception if logging was not successful like file not found
     */
    public static function log($data, $type = LOG_TYPE_DEBUG)
    {
        try {

            $logger = self::getComponent('log');

            if ($logger instanceof Logger) {
                $logger->write($data, $type);
            } else {
                throw new LogException('Logger is not an instance of Interface/Logger');
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Alias for Leo::getComponent()
     * @param string $component_name
     * @param array $params initialising properties
     * @param boolean $throwException should exception be thrown if the component is not found
     * @return object instantiated component object
     * @throws Exception
     */
    public static function gc($component_name, array $params = null, $throwException = true)
    {
        return self::getComponent($component_name, $params, $throwException);
    }

    /**
     * Load a config component but don't store constructed object in component static storage
     * @param $component_name
     * @param array $properties
     * @param bool $throwException
     * @return object
     * @throws Exception
     */
    public static function lc($component_name, $properties = [], $throwException = true)
    {
        self::log('Loading new component with name '.$component_name);

        $component_object = self::loadComponent(
            self::getConfig($component_name,self::COMPONENTS,$throwException), $properties
        );

        return $component_object;
    }

    /**
     * Get request component
     * @return Request
     * @throws Exception
     */
    public function getRequest()
    {
        return self::getComponent('request');
    }

    /**
     * Get application logger
     * @return Writer
     * @throws Exception
     */
    public function getLogger()
    {
        return self::getComponent('log');
    }

    /**
     * Get request component
     * @return Session
     * @throws Exception
     */
    public function getSession()
    {
        return self::getComponent('session');
    }

    /**
     * Returns the class that constructs and run database queries
     * @return Constructor
     * @throws Exception
     */
    public function getDb()
    {
        return Leo::reloadComponent('queryConstructor');
    }

    /**
     * Returns package manager
     * @return \Leo\Packages\PackageManager
     * @throws Exception
     */
    public function getPackageManager()
    {
        return self::getComponent('packageManager');
    }

    /**
     * Returns package manager
     * @return \Leo\Event\EventManager
     * @throws Exception
     */
    public function getEventManager()
    {
        return self::getComponent('eventManager');
    }

    /**
     * Returns dependency injector and service locator
     * @return \Leo\Di\Di
     * @throws Exception
     */
    public function getDi()
    {
        return self::getComponent('di');
    }

    /**
     * Returns the route for the home url of website.
     * Change the config value of homeUrl to change default
     * @return array
     * @throws Exception
     */
    public function getHomeUrl()
    {
        return $this->getDomainManager()->get('homeUrl');
    }

    /**
     * Returns the base class for format
     * @return \Leo\Helpers\Formatter\Base
     * @throws Exception
     */
    public function getFormatter()
    {
        return self::getComponent('formatter');
    }

    /**
     * Returns the class use to manage interaction of domain configuration
     * @return DomainManager
     * @throws LogException
     */
    public function getDomainManager(){
        return self::getComponent('domainManager');
    }

    /**
     * @return Theme
     * @throws Exception
     */
    public function getTheme(){
        return self::getComponent('theme');
    }

    /**
     * Returns the base class for translations
     * @param string $key the string you want translated
     * @return \Leo\Intl\Intl
     * @throws Exception
     */
    public function t($key, $lang='en')
    {
        return self::getComponent('lang')->setLang($lang)->translate($key);
    }

    /**
     * @param string $tableName Table Name
     * @return TableInfo
     * @throws Exception
     */
    public function getTableInfo($tableName)
    {
        return Leo::rc('tableInfo', array('name' => $tableName));
    }

    public static function version()
    {
        return '2.6';
    }

}
