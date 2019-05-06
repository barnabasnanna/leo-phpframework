<?php

namespace Leo\Interfaces;

/**
 * Interface for package manager
 * @author barnabasnanna
 * Date 1/1/16
 */
interface I_PackageManager
{

    /**
     * Enable of disable packages in user application
     * 
     */
    public function allowUserPackages();

    /**
     * Enable or disable packages found in Leo framework
     */
    public function allowLeoPackages();

    /**
     * Get configuration of all enabled packages
     */
    public function getAllPackageConfigs();

    /**
     * Is a package enabled
     */
    public function isPackageEnabled($package_name = '');

    /**
     * Returns the configuration of a package
     * @param string $package_name Package name
     */
    public function getPackageConfig($package_name = '');
    
    /**
     * Does a package exist
     * @param string $package_name
     */
    public function packageExists($package_name = '');
    
    /**
     * Returns all the package routes
     */
    public function getPackagesRoutes();
    
}
