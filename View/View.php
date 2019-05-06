<?php
namespace Leo\View;

use Leo\Leo;
use Leo\Routing\Route;

/**
 * @file
 * Renders the view file
 * @author Barnabas
 */
class View
{

    private $viewFile;
    private $layout;
    private $params;

    public function __construct($viewName = '', array $params = [], $theme = null)
    {
        $this->viewFile = $viewName;
        $this->params = $params;
        Leo::setTheme($theme);//set the theme
        $this->route = Leo::getComponent('router')->getRoute();
    }

    /**
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }
    
    public function getParams()
    {
        return $this->params;
    }


    /**
     * View file extension
     * @return string
     */
    public function getViewFileExtension()
    {
        return $this->route->getFileExtention();
    }

    /**
     * View file content
     * @param array $params
     * @param string $view
     * @return string
     */
    public function output(array $params = array(), $view = '')
    {
        ob_start();
             $viewFile = $this->getLayoutControllerFile($view);
             extract($params);
             require $viewFile.$this->getViewFileExtension();
         return ob_get_clean();
    }

    /**
     * Locate the view file that has content.
     * If route has a base defined, check base folder.
     * If no base defined and module exist, check in module view folder
     * If theme, check in theme folder.
     *
     * If file not found in theme folder,
     * @param string $view
     * @param string $base
     * @return string
     * @throws \Exception
     */
    public function getLayoutControllerFile($view = '', $base='')
    {
        $viewFileName = $view ? $view : $this->viewFile;

        //if there exist a base path from router array use, else if module use
        $baseBath = $base ? $base : ( $this->route->getBasePath()?: ( $this->route->getModule() ? 'Modules'.DS.ucfirst($this->route->getModule()).DS :''));

        $viewFolder = $themeFolder = static::getThemeFolder($baseBath);

        if(!is_dir($themeFolder)){//if theme or package base directory doesn't exist
           $viewFolder = static::getViewFolder($baseBath);
        }

        $file = $viewFolder.DS.strtolower($this->route->getController()).DS.$viewFileName;
        
        return $file;

    }

    public static function getThemeFolder(){

        $themeFolder = null;
        $base = '';

        if($themeName = \leo()->getTheme()->getName())
        {//theme in config
            $themeFolder = APP_PATH.DS.$base.'Themes';
            $themeFolder = APP_PATH.DS.($base?$base.DS : '') .'Themes'.DS.$themeName;
        }

        return $themeFolder;
    }

    /**
     * Return the views folder where layout view files should be searched
     * @param string $base
     * @return string
     * @throws \Exception
     */
    public static function getViewFolder($base = '')
    {
        $viewFolder = APP_PATH.DS.$base.'Views';

        return $viewFolder;
    }
    
    /**
     * Return the layout folder location. If a base path is given,
     * it returns the layout folder in the base path location
     * @param string $base
     * @return string
     */
    public static function getLayoutFolder($base='')
    {
        return static::getViewFolder($base).DS.'layout';
    }

    /**
     * Get a layout file
     * @param $layoutFile
     * @return string
     * @throws \Exception
     */
    public static function getLayoutFile($layoutFile)
    {
        /**
         * @var Route $route
         */
        $route = Leo::gc('router')->getRoute();
        
        if(!file_exists(//packages have base basepath
                $layout_file = static::getLayoutFolder($route->getBasePath())
                    . DS . $layoutFile.$route->getFileExtention()
            )
            AND
            !file_exists(
                $layout_file = static::getLayoutFolder().
                    DS . $layoutFile.
                    $route->getFileExtention()
            )

        )
        {
            throw new \Exception($layout_file . ' could not be found.');
        }
        
        return $layout_file;
    }

    /**
     * Use to include a file within a file
     * @param string $view file you want include
     * @param array $params parameters you want passed to view file
     * @return string
     */
    public function includeFile($view = '' , array $params = [])
    {
        return $this->output($params,$view);
    }

    /**
     * @var Route
     */
    private $route;

    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }
        
    

}
