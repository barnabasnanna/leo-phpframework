<?php

namespace Leo\Packages;

use DirectoryIterator;
use Leo\Exception\PackageConfigException;
use Leo\Exception\PackageMissFileException;
use Leo\Interfaces\I_PackageManager;
use Leo\Leo;
use Leo\ObjectBase;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * PackageManager is responsible for loading all site packages and their assets
 *
 * @author barnabasnanna
 */
class PackageManager extends ObjectBase implements I_PackageManager
{

    protected $config = null;
    protected $package_routes = null;
    protected $package_names = null;
    protected $package_event_handlers = null;
    protected static $css = [];
    protected static $js = [];
    protected static $links = [];

    function getLeoPackagesFolder()
    {
        return CORE_PATH . DS . 'Packages';
    }

    function getUserPackagesFolder()
    {
        return APP_PATH . DS . 'Packages';
    }
    
    function addLink(array $link)
    {
        static::$links[] = $link;
    }
    
    function renderLinks()
    {
        $links='';
        if(count(static::$links))
        {
            foreach(static::$links as $link)
            {
                foreach ($link as $key=>$value){
                    $links.= "$key='$value'";
                }
            }
            $links = '<link '.$links.'>';
        }
        return $links;
    }

    /**
     * All packages css
     * @return array
     */
    public static function getCss()
    {
        return self::$css;
    }

    /**
     * All packages js
     * @return array
     */
    public static function getJs()
    {
        return self::$js;
    }

    public static function setCss($css)
    {
        self::$css[$name] = $css;
        return self;
    }

    public static function setJs($js)
    {
        self::$js[$name] = $js;
        return self;
    }

    public function allowLeoPackages()
    {
        return \leo()->getDomainManager()->get(__FUNCTION__);
    }

    public function allowUserPackages()
    {
        return \leo()->getDomainManager()->get(__FUNCTION__);
    }

    /**
     * Package configuration after merging user and that of framework. 
     * User packages config overrides Leo pacakges config.
     * @return array combined configuration
     */
    public function getAllPackageConfigs()
    {

        if ($this->config === null)
        {
            //get each user package config
            $app = $this->allowUserPackages() ? $this->getUserPackagesConfig() : array();

            //get each leo package config
            $leo = $this->allowLeoPackages() ? $this->getLeoPackagesConfig() : array();

            $this->config = array_replace_recursive($leo, $app);
        }
        
        return $this->config;
    }

    /**
     * Returns the routes of all the packages
     * @return array
     * @throws PackageConfigException
     */
    public function getPackagesRoutes()
    {

        if ($this->package_routes === null)
        {
            leo()->getLogger()->write('Extracting package routes');
            $this->package_names = [];
            $this->package_routes = [];

            foreach ($this->getAllPackageConfigs() as &$packagesArray)
            {
                foreach ($packagesArray as $package_name => &$package_config)
                {
                    if (isset($this->package_names[$package_name]))
                    {
                        //TODO Write a handler for this exception
                        throw new PackageConfigException(
                                "Package name duplication. $package_name already exists."
                                );
                    }

                    //store package names
                    $this->package_names[] = $package_name;

                    //Is the package enabled and has routes
                    if ((isset($package_config['enabled']) && $package_config['enabled'])
                        && (isset($package_config['routes'])
                            && is_array($package_config['routes'])))
                    {
                        //merging routes of packages. latter rules replaces old rules
                        $this->package_routes = array_replace(
                                $this->package_routes, $package_config['routes']);
                    }
                }
                unset($package_config);
            }
            unset($packagesArray);
        }
        return $this->package_routes;
    }

    /**
     * Returns the event handlers of all the packages
     * @return array all event handlers merged from all package configs
     * @throws PackageConfigException
     */
    public function getPackagesEventHandlers()
    {

        if ($this->package_event_handlers === null)
        {
            leo()->getLogger()->write('Extracting event handlers config from packages');
            $this->package_names = [];
            $this->package_event_handlers = [];

            foreach ($this->getAllPackageConfigs() as $packages)
            {
                foreach ($packages as $package_name => $package_config)
                {
                    if (isset($this->package_names[$package_name]))
                    {
                        throw new PackageConfigException(
                            "Package name duplication. $package_name already exists."
                        );
                    }

                    //store package names
                    $this->package_names[] = $package_name;

                    if ((isset($package_config['enabled']) && $package_config['enabled']) && isset($package_config['event_handlers']) && is_array($package_config['event_handlers']))
                    {
                        //merging event handlers of packages. latter rules replaces old rules
                        $this->package_event_handlers = array_merge_recursive(
                            $this->package_event_handlers, $package_config['event_handlers']);
                    }
                }
            }
        }

        return $this->package_event_handlers;
    }

    public function getPackageNames()
    {
        return $this->package_names;
    }

    /**
     * Leo packages configuration
     * @return array
     */
    private function getLeoPackagesConfig()
    {
        leo()->getLogger()->write('Getting leo package config');

        $folder = $this->getLeoPackagesFolder();

        $dir = new DirectoryIterator($folder);

        $all_leo_packages_config = [];

        foreach ($dir as $fileinfo)
        {
            if ($fileinfo->isDir()  && !$fileinfo->isDot())
            {
                $package_config_file = $folder . DS . $fileinfo->getFilename() . DS . 'Config' . DS . 'config.php';

                if (is_readable($package_config_file))
                {
                    $package_config = require $package_config_file;

                    if (is_array($package_config))
                    {
                        $packaged_enabled = \array_column($package_config,'enabled');
                        if($packaged_enabled && $packaged_enabled[0]) {
                            $all_leo_packages_config[] = $package_config;
                        }
                    }
                }
            }
        }

        return $all_leo_packages_config;
    }

    /**
     * user packages configuration
     * @return user config
     */
    private function getUserPackagesConfig()
    {

        leo()->getLogger()->write('Getting user package config');

        $dir = new \DirectoryIterator($this->getUserPackagesFolder());

        $all_user_app_packages_config = [];

        foreach ($dir as $fileinfo)
        {

            if ($fileinfo->isDir() && !$fileinfo->isDot())
            {

                $package_config_file = $this->getUserPackagesFolder() . DS . $fileinfo->getFilename() . DS . 'Config' . DS . 'config.php';

                if (\is_readable($package_config_file))
                {
                    $package_config = require $package_config_file;

                    if (is_array($package_config)){

                        $packaged_enabled = \array_column($package_config,'enabled');

                        if($packaged_enabled && $packaged_enabled[0])
                        {
                            $all_user_app_packages_config[] = $package_config;
                        }
                    }
                }
            }
        }

        return $all_user_app_packages_config;
    }

    /**
     * Get the config of a particular package
     * @param string $package_name
     * @return array|false returns the package config array or FALSE if not found
     */
    public function getPackageConfig($package_name = '')
    {
        $all_configs = $this->getAllPackageConfigs();
        $config = false;

        foreach ($all_configs as $package_config)
        {
            if (isset($package_config[$package_name]))
            {
                $config = $package_config[$package_name];
                break;
            }
        }

        return $config;
    }

    /**
     * Is a package enabled
     * @param string $package_name package name
     * @return boolean true if enabled
     */
    public function isPackageEnabled($package_name = '')
    {
        if (is_array(($package_config = $this->getPackageConfig($package_name))))
        {
            return isset($package_config['enabled']) ? !!$package_config['enabled'] : false;
        }

        return false;
    }

    /**
     * Checks if a package with a certain name exists
     * @param string $package_name
     * @return bool true if exists else false
     */
    public function packageExists($package_name = '')
    {
        return \in_array($package_name, $this->getPackageNames());
    }

    
    /**
     * Return all package css file to be added to page
     * @return string
     */
    public function renderCssFiles()
    {
        $cssfiles = '';
        foreach (self::$css as $cssFile)
        {
           $cssfiles .= '<link href="' . DS . 'packages' . DS .$cssFile.'" rel="stylesheet">'."\n";
        }
        
        return $cssfiles;
    }
    
    /**
     * Return all package js files to be added to page
     * @return string
     */
    public function renderJsFiles()
    {
        $jsfiles = '';
        
        foreach (self::$js as $jsFile)
        {
            $jsfiles.= '<script type="text/javascript" '
                    . 'src="'.DS.'packages' . DS . $jsFile.'"></script>'."\n";
        }
        
        return $jsfiles;
    }

    /**
     * Returns the package www css folder
     * @param string $package_name
     * @return string
     * @throws \Exception
     */
    public function getPackageWWWCssFolder($package_name)
    {
        $package_css_folder = WEB_ROOT . DS . 'packages' . DS . $package_name . DS . 'css';

        if (!file_exists($package_css_folder))
        {
            if (!mkdir($package_css_folder, 0775, true))
            {
                throw new \Exception('Could not create package folder ' . $package_css_folder . ' Make sure'
                . ' path is writable.');
            }
        }

        return $package_css_folder;
    }

    /**
     * Returns the package www js folder
     * @param string $package_name
     * @return string
     * @throws \Exception
     */
    public function getPackageWWWJsFolder($package_name)
    {
        $package_js_folder = WEB_ROOT . DS . 'packages' . DS . $package_name . DS . 'js';

        if (!file_exists($package_js_folder))
        {
            if (!mkdir($package_js_folder, 0775, true))
            {
                throw new \Exception('Could not create package folder ' . $package_js_folder . ' Make sure'
                . ' path is writable.');
            }
        }

        return $package_js_folder;
    }

    public function getPackageAssetsPath($package_name = '')
    {
        //asset base path
        $package_config = $this->getPackageConfig($package_name);
        if (is_array($package_config) && isset($package_config['base']))
        {
            $assets_path = DS . $package_config['base'] . DS . 'Assets';

            if (file_exists($this->getUserPackagesFolder() . $assets_path))
            {
                return $this->getUserPackagesFolder() . $assets_path;
            }
            else
            {
                return $this->getLeoPackagesFolder() . $assets_path;
            }
        }
    }

    /**
     * Transfer a package file to right css or js public location
     * @param string $from_package_location file you want contents transferred
     * @param sting $to_public_location new file created with contents
     * @throws PackageMissFileException if file could not be transferred
     */
    private function transferPackageFile($from_package_location, $to_public_location)
    {
        //if does not already exists add
        if (LEO_RUNNING_MODE == 'development' OR !file_exists($to_public_location))
        {
            leo()->getLogger()->write("File transfer from ($from_package_location) to ($to_public_location)");
            
            $css_file_contents = file_get_contents($from_package_location);

            if (!file_put_contents($to_public_location, $css_file_contents))
            {
                throw new PackageMissFileException("$to_public_location "
                . "could not be transferred to public"
                . " assets folder");
            }
        }
    }

    /**
     * Add package css to be render
     * @param string $package_name package name
     * @param string $filename css file name in assets css folder
     * @param string $savename css file name used to save the file in www package folder
     * @throws PackageMissFileException if file is missing
     * @throws PackageConfigException if there is an issue with package config
     */
    public function addCss($package_name, $filename = '', $savename='')
    {
        if ($this->packageExists($package_name))
        {
            $package_css_file_name = $this->getPackageAssetsPath($package_name) . DS . 'css' . DS . $filename;

            if (file_exists($package_css_file_name))
            {
                $package_www_css_filename = $this->getPackageWWWCSSFolder($package_name) . DS . ($savename ?: $filename);

                $this->transferPackageFile($package_css_file_name, $package_www_css_filename);

                self::$css[] = $package_name.DS.'css'.DS.($savename ?: $filename);
            }
            else
            {
                $e_message = lang("Packages css file named $package_css_file_name not found");
                throw new PackageMissFileException($e_message);
            }
        }
        else
        {
            $e_message = lang("Package with package name ($package_name) does not exist.");
            throw new PackageConfigException($e_message);
        }
    }

    /**
     * 
     * @param string $package_name Package name
     * @param string $filename name of assets file
     * @param string $savename name used to save file in www package folder
     * @throws PackageMissFileException
     * @throws PackageConfigException
     */
    public function addJs($package_name, $filename = '', $savename='')
    {
        if ($this->packageExists($package_name))
        {
            $package_js_file_name = $this->getPackageAssetsPath($package_name) . DS . 'js' . DS . $filename;

            if (file_exists($package_js_file_name))
            {
                $package_www_js_filename = $this->getPackageWWWJSFolder($package_name) . DS . ($savename ?: $filename);

                $this->transferPackageFile($package_js_file_name, $package_www_js_filename);
                
                self::$js[] = $package_name.DS.'js'.DS. ($savename ?: $filename);
            }
            else
            {
                throw new PackageMissFileException(
                        "Packages js file named $package_js_file_name not found"
                        );
            }
        }
        else
        {
            throw new PackageConfigException(
                    "Package with package name ($package_name) does not exist."
                    );
        }
    }


}
