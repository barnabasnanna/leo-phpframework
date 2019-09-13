<?php
/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 06/12/2018
 * Time: 00:36
 */

namespace Leo\Helpers;


use Leo\Leo;
use Leo\ObjectBase;

class DomainManager extends ObjectBase
{
    protected $base = null;

    public function getBase(){
        return $this->base?:DOMAIN_NAME;
    }

    public function setBase($base){
        $this->base = strval($base);
    }

    /**
     * Return the config of the domain. Exception if not found
     * @param mixed|string $domain
     * @return mixed
     * @throws \Exception
     */
    public function getDomainSettings($domain=DOMAIN_NAME){
        return Leo::getConfig($domain, 'domains');
    }

    /**
     * Get a value from the domain's config array
     * @param $name
     * @param string $domain
     * @return mixed|null null if not found
     * @throws \Exception
     */
    public function get($name, $domain=DOMAIN_NAME){
        $settings = $this->getDomainSettings($domain?:$this->getBase());
        return isset($settings[$name]) ? $settings[$name] : null;
    }

    public function getDbConnection($connectionName, $domain=DOMAIN_NAME){
        $dbSettings = $this->get('db',$domain?:$this->getBase());
        return isset($dbSettings) && isset($dbSettings['connections'][$connectionName]) ?
            $dbSettings['connections'][$connectionName] : null;
    }
}