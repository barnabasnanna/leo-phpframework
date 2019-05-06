<?php
namespace Leo\Http;

use Leo\ObjectBase;

/**
 * Stores all information regarding user request
 *
 * @author Barnabas
 */
class Request extends ObjectBase
{

    private $request_url = null;
    private $params = null;
    protected $resolved = NULL;

    public function _start_()
    {
        $a = parse_url($_SERVER['REQUEST_URI']);
        parse_str( (isset($a['query']) ? $a['query'] : '') , $this->params);
    }

    /**
     * Return requested url
     * 
     * @return string
     */
    public function getUrl()
    {
        
        if (is_null($this->request_url)) 
        {
            $url = !empty($_GET['url']) ? filter_input(INPUT_GET, 'url') : '';
            $this->request_url = \rtrim(strtolower($url), '/');
        }
        
        return $this->request_url;
        
    }

    /**
     * Get all the post params
     * @return array
     */
    public function getPosts(){
        return $_POST ?:[];
    }

    public function getParams()
    {
        return $this->params;
    }

    /**
     * Merge the post and the get parameters
     * @return array
     */
    public function allParams(){
        return array_merge($this->params, $this->getPosts());
    }
    
    public function getHost()
    {
        return $_SERVER['HTTP_HOST'];
    }
    
    public function getProtocol()
    {
        return stripos($_SERVER['SERVER_PROTOCOL'],'https') !==FALSE ? 'https://' : 'http://';
    }

    public function getReferrer()
    {
        return isset($_SERVER['HTTP_REFERRER']) ? $_SERVER['HTTP_REFERRER'] : NULL;
    }

    public function getBaseName()
    {
       return $this->getProtocol().$this->getHost();
    }

    /**
     * Return the url of the webpage being viewed
     * @return string
     */
    public function getPageUrl()
    {
        return $this->getUrl()."?".http_build_query($this->getParams());
    }


    /**
     * Get a param firstly checking in the $_GET param then $_POST param. If not found
     * the default is returned
     * @param string $name param name
     * @param mixed $default value return if not found
     * @return mixed null or user provided value
     */
    public function getParam($name,$default=null)
    {
        $params = $this->getParams();
        return FALSE===array_key_exists($name, $params) ?
        $this->post($name,$default) : $params[$name];
    }


    /**
     * Retrieve a get parameter
     * @param string $key
     * @param mixed $default null
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->getParams()[$key]) ? $this->getParams()[$key] : $default;
    }
        
    /**
     * Retrieve a post param
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function post($key, $default = null)
    {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }


    public function isPost()
    {
        return \strtolower($_SERVER['REQUEST_METHOD']) === 'post';
    }
    
    public function isGet()
    {
        return \strtolower($_SERVER['REQUEST_METHOD']) === 'get';
    }
    
    public function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	
    }
    
    public function setParams(array $params)
    {
        if($this->params===NULL)
        {
            $this->params = $params;
        }
        else
        {
            $this->params = array_merge($this->params, $params);
        }
    }
    
}
