<?php
namespace Leo\Exception;

use Leo\Leo;

/**
 * Handles uncaught exception thrown by application
 *
 * @author Barnabas
 */
class Handler extends \Exception
{
    public static function handlerException($exception)
    {
        
        $message = "Uncaught exception: " . $exception->getMessage().PHP_EOL.
                $exception->getTraceAsString(). "\n";
        
        Leo::gc('log')->write($message, LOG_TYPE_APP_ERROR);
        
        if(LEO_RUNNING_MODE == 'development')
        {
            static::displayError($exception->getMessage(), $exception->getTraceAsString());
        }
        
        die(lang('Application offline. Do contact site administrators.'));
    }

    public static function displayError($message, $traceString)
    {
        echo '<pre>';
        echo $message;
        echo PHP_EOL;
        echo $traceString;
        echo '</pre>';
    }
}
