<?php
namespace Leo\Controller;

use app\Models\User;
use app\Models\Users;
use Exception;
use Leo\Http\Request;
use Leo\Leo;
use Leo\Models\Me;
use Leo\ObjectBase;
use Leo\View\View;

/**
 * All Controllers should extend this
 *
 * @author Barnabas
 */
abstract class BaseController extends ObjectBase
{
    /**
     *
     * @var string A custom layout file used to wrap view files before rendering
     */
    protected $layoutFile;

    /**
     * Default action called if none set in requested route
     * @var string
     */
    protected $defaultAction = false;

    /**
     * A temp container to store data during processing page request
     *
     * @var array
     */
    protected $_class_data_ = [];

    /**
     * @var View
     */
    protected $viewClass = null;

    /**
     * @return string
     */
    public function getLayoutFile()
    {
        return $this->layoutFile;
    }

    public function setLayoutFile($layoutFile='')
    {
        $this->layoutFile = $layoutFile;
    }

    public function getClassName()
    {
        return get_called_class();
    }

    /**
     * Add data to temp class storage
     * @param string $name data stored key
     * @param mixed $value data stored value
     * @param bool $override can the data be overriden if already exist in storage
     * @throws Exception
     */
    public function addData($name, $value, $override = false)
    {
        if (!$override && array_key_exists($name, $this->getClassData())) {
            throw new Exception($name . lang(' already exists in data storage.'));
        }

        $this->_class_data_[$name] = $value;
    }

    /**
     * A temp storage used to pass data around.
     * @return array
     */
    public function getClassData()
    {
        return $this->_class_data_;
    }

    /**
     * Get data from class storage
     * @param string $name data storage key
     * @param mixed $default value if name doesnt exists
     * @param bool $throwException should an exception be thrown if name does not exist
     * @return mixed data value or default value
     * @throws Exception
     */
    public function getData($name, $default = null, $throwException= false)
    {
        if(array_key_exists($name, $this->getClassData()))
        {
            return $this->_class_data_[$name];
        }
        elseif($throwException)
        {
            throw new \Exception($name . lang(" not found in controller data"));
        }

        return $default;
    }


    /**
     * Remove data from class storage
     * @param string $name data key you want removed
     */
    public function removeData($name = '')
    {
        if (array_key_exists($this->_class_data_[$name])) {
            unset($this->_class_data_[$name]);
        }
    }

    /**
     * Refresh page
     */
    public function refresh()
    {
        header("Location: /".leo()->getRequest()->getPageUrl());
        exit;
    }


    /**
     * Redirects browser to another page
     * The first element is used for routing while other parts of the array are used as
     * parameters
     * @param array $url_paths
     * @param bool $replace
     * @throws Exception
     */
    public function redirect(array $url_paths, $replace=true)
    {//TODO add protocol from $_SERVER variable
        leo()->getRouter()->redirect($this->getHref($url_paths), $replace);
    }

    /**
     * Default action to run if not set.
     *
     * @return string
     * @throws Exception
     */
    public function getDefaultAction()
    {
        return !$this->defaultAction ? ( Leo::getConfig('defaultAction')?:'index' ) : $this->defaultAction;
    }

    /**
     * Action name
     * @param string $action
     */
    public function setDefaultAction($action)
    {
        $this->defaultAction = $action;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getActionPrefix()
    {
        return Leo::getConfig('actionPrefix');
    }

    /**
     * Return href link
     * @param array $url
     * @return string
     */
    protected function getHref(array $url)
    {
        if(!is_array($url)) return '';
        $href = array_shift($url);
        return $href.( !empty($url) ? '?'. http_build_query($url) : '');

    }

    /**
     * Load the view file and store the content
     * @param string $view view file path
     * @param array $params parameters to be passed to the view file
     * @param string $theme Theme name
     * @throws Exception
     */
    public function view($view = '', array $params = [], $theme=null)
    {
        if($view) {

            $this->viewClass = new \Leo\View\View($view, $params, $theme);

            Leo::$content = $this->addCustomLayoutFile($this->viewClass->output($params));
        }

    }

    /**
     * Send a json encoded response
     * @param array $data
     * @throws Exception
     */
    public function json(array $data)
    {
        leo()->getRouter()->getRoute()->setResponseType('json');

        $result = \json_encode($data);

        if($result === false)
        {
            Leo::$content = json_encode(array('jsonError', json_last_error_msg()));
            http_response_code(500);
        }
        else
        {
            Leo::$content = $result;
        }
    }

    /**
     * If the controller has a custom layout file add its make up
     * @param string $content content of the view file from controller
     * @return string
     * @throws Exception
     */
    private function addCustomLayoutFile($content = '')
    {
        return $this->render($this->getLayoutFile(), $content);
    }

    /**
     * Used to include files in wrapper layout files
     * @param string $layoutFile
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function includeLayoutFile($layoutFile = '' , array $params = [])
    {
        $layout_file = $this->viewClass->getLayoutFile($layoutFile);

        ob_start();
        extract($params);
        include $layout_file;
        return ob_get_clean();

    }

    /**
     * Tasks to run before controller action
     * Request type check
     * Authentication check
     * Callable methods defined in route config
     * @return boolean return true to run action else false to prevent action from running
     * @throws Exception
     */
    public function runBeforeAction()
    {
        //verb check
        return $this->checkAccessMethodIsValid(Leo::getComponent('request'))
            &&
            //auth check
            $this->checkAuthentication()
            &&
            //route before Action callable methos
            $this->runRouteCallBackMethods('beforeAction');

    }

    /**
     * Send response to the browser
     */
    public function sendResponse()
    {
        if (Leo::$content) {
            switch (\current(Leo::getComponent('router')->getRoute()->getResponseType())) {

                case 'json':
                    if (!leo()->getRequest()->get('devdebug')) {//if ?devdebug is set
                        header('Content-Type: application/json;charset=utf-8');
                    }
                    echo Leo::$content;
                    break;
                default:
                    echo $this->render('page', Leo::$content);
                    break;
            }
        }

    }

    /**
     * Call any after Action
     * @throws Exception
     */
    public function runAfterAction(){
        $this->runRouteCallBackMethods('afterAction');
    }

    /**
     * Check that the request type matches that defined in route if any
     * @param Request $request
     * @return bool
     * @throws Exception
     */
    private function checkAccessMethodIsValid(Request $request)
    {
        $routeVerbs = Leo::gc('router')->getRoute()->getVerb();

        if (!empty($routeVerbs)) {
            $verb = [];
            if ($request->isGet()) {
                $verb[] = 'get';
            }// if accessed by Get
            elseif ($request->isPost()) {
                $verb[] = 'post';
            } //if access by Post
            if ($request->isAjax()) {
                $verb[] = 'ajax';
            } //if accessed by Ajax
            /*
             * Ensure request access method is valid for route
             */
            if (!count(\array_intersect($verb, $routeVerbs))) {
                throw new \Exception('Page can only be accessed with verbs (' . \implode(',', Leo::gc('router')->getRoute()->getVerb()).')');
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws Exception
     * @throws LogException
     */
    private function checkAuthentication()
    {
        $accessDenied = FALSE;

        if (!empty($authMethods)) {

            $accessDenied = TRUE;

            if (Leo::checkConfigExists('auth')) {

                $auth=null;

                if ($auth = leo()->getComponent('auth') && \method_exists($auth, 'checkAccess')) {

                    foreach (Leo::gc('router')->getRoute()->getAuth() as $accessLevelString) {

                        if (TRUE !== $auth->checkAccess($accessLevelString)) {
                            $accessDenied = true;
                            break;
                        }
                    }

                } else {
                    //TODO - implement RBAC
                    throw new \Exception('Auth component does not have a checkAccess() method');
                }
            }
        }

        return FALSE === $accessDenied;
    }

    /**
     * Call the before and after callable actions signified in a route config
     * @param string $which
     * @return bool
     * @throws Exception
     */
    private function runRouteCallBackMethods($which='beforeAction')
    {
        $returnValue = true;

        $route = Leo::gc('router')->getRoute();

        $callables = ($which==='beforeAction') ? $route->getBeforeAction() : $route->getAfterAction();

        try {
            if ($callables) {
                try {
                    if (is_array($callables))
                        foreach ($callables as $callable) {
                            if (!is_callable($callable)) {
                                throw new \Exception('Route before callable not all valid');
                            }
                            $returnValue = $returnValue && call_user_func($callable);
                        }

                } catch (Exception $e) {
                    throw $e;
                }

            }
        } catch (Exception $e) {

        }

        return $returnValue;

    }

    /**
     * Render a layout file
     * @param null $layoutFile
     * @param string $content
     * @return string
     * @throws Exception
     */
    public function render($layoutFile = null, $content = '')
    {

        if ($layoutFile) {
            ob_start();
            $view = $this->viewClass ?: new View();
            include $view->getLayoutFile($layoutFile);
            $content = ob_get_clean();
        }

        return $content;

    }

}