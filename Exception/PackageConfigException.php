<?php
namespace Leo\Exception;

use Leo\Exception\Handler;


/**
 * PackageException handles exceptions relating to missing package config
 *
 * @author barnabasnanna
 * Date 31/12/15
 */
class PackageConfigException extends Handler
{
    public function __construct($message = '' )
    {
        parent::__construct($message);
        
        leo()->getLogger()->write($message, LOG_TYPE_APP_ERROR);
        
        if(LEO_RUNNING_MODE === 'development')
        {
            echo $this->getMessage();
            echo PHP_EOL;
            echo $this->getTraceAsString();
        }
    }

}
