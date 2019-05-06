<?php
namespace Leo\Exception;

/**
 * MissingConfigException handles exceptions relating to missing config values
 *
 * @author barnabasnanna
 * Date 31/12/15
 */
class MissingConfigException extends Handler
{
    /**
     * 
     * @param string $message
     * @throws type
     */
    public function __construct($message = '', $component_name)
    {
        parent::__construct($message);
        
        if('log'!=$component_name) {
            leo()->getLogger()->write($message, LOG_TYPE_APP_ERROR);
        }

        if(LEO_RUNNING_MODE == 'development')
        {
            static::displayError($this->getMessage(), $this->getTraceAsString());
        }

        //TODO EMAIL ADMIN AND SHOW A 550 PAGE INSTEAD
        die();
    }
}
