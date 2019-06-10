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
    private $themeFolder;

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
     * @throws \Exception
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
     * If file not found in theme folder, check View folder
     * @param string $view
     * @param string $base
     * @return string
     * @throws \Exception
     */
    public function getLayoutControllerFile($view = '', $base='')
    {
        $file = null;

        $viewFileName = $view ? $view : $this->viewFile;

        //if there exist a base path from router array use, else if module use
        $basePath = $this->getBasePath($base);

        $viewFolder = $this->themeFolder = $this->getThemeFolder($basePath);

        if(is_dir($this->themeFolder)){//does the Theme directory exist
            $file = $viewFolder.DS.strtolower($this->route->getController()).DS.$viewFileName;
            if(!is_readable($file)){//does the file exist in theme folder
                $file = null;
            }
        }

        if($file===null && !is_dir($this->themeFolder)) {//if theme or package base directory doesn't exist
            $this->themeFolder = null;
            $viewFolder = $this->getViewFolder($basePath);
            $file = $viewFolder . DS . strtolower($this->route->getController()) . DS . $viewFileName;
        }

        return $file;

    }

    public function getBasePath($base = ''){
        //if there exist a base path from router array use, else if module use
        $basePath = $base ? $base : ( $this->route->getBasePath()?: ( $this->route->getModule() ? 'Modules'.DS.ucfirst($this->route->getModule()) :''));
        return $basePath;
    }

    /**
     * Get the theme folder, Themes, in base path. Base path can be in Package
     * @param string $base
     * @return null|string
     * @throws \Exception
     */
    public function getThemeFolder($base=''){

        $themeFolder = null;

        if($themeName = \leo()->getTheme()->getName())
        {//theme in config
            $themeFolder = str_replace(DS.DS,DS,APP_PATH.DS.($base? $base.DS : '')) .'Themes'.DS.$themeName;
        }

        return $themeFolder;
    }

    /**
     * Return the views folder where layout view files should be searched.
     * If a theme is being used, The theme folder is used else app/Views folder is used
     * @param string $base
     * @return string
     * @throws \Exception
     */
    public function getViewFolder($base = '')
    {

        if(!$this->themeFolder) {
            $viewFolder = APP_PATH . DS . $base . DS . 'Views';
        } else {
            $viewFolder = $this->themeFolder;
        }

        return $viewFolder;
    }

    /**
     * Return the layout folder location. If a base path is given,
     * it returns the layout folder in the base path location
     * @param string $base
     * @return string
     * @throws \Exception
     */
    public function getLayoutFolder($base='')
    {
        return $this->getViewFolder($base).DS.'layout';
    }

    /**
     * Get a layout file
     * @param $layoutFile
     * @return string
     * @throws \Exception
     */
    public function getLayoutFile($layoutFile)
    {
        /**
         * @var Route $route
         */
        $route = Leo::gc('router')->getRoute();

        if(!file_exists(//packages have base basepath
                $layout_file = $this->getLayoutFolder($route->getBasePath())
                    . DS . $layoutFile.$route->getFileExtention()
            )
            AND
            !file_exists(
                $layout_file = $this->getLayoutFolder().
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
