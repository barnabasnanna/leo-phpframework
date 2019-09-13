<?php
namespace Leo\Exception;

use Leo\Exception\Handler;

/**
 * MissingConfigException handles exceptions relating to missing config values
 *
 * @author barnabasnanna
 * Date 31/12/15
 */
class PackageMissFileException extends Handler
{
    /**
     * 
     * @param string $message
     * @throws type
     */
    public function __construct($message = '' )
    {
        parent::__construct($message);
        
        leo()->getLogger()->write($message, LOG_TYPE_APP_ERROR);
        
        //TODO inform package developer
        if(LEO_RUNNING_MODE == 'development')
        {
            echo $this->getMessage();
            echo PHP_EOL;
            echo $this->getTraceAsString();
        }
    }
}
