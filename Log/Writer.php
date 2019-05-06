<?php
namespace Leo\Log;

use Leo\ObjectBase;
use Leo\Interfaces\Logger;

/**
 * Writer is used to write log information to file
 * @author Barnabas
 */
class Writer extends ObjectBase implements Logger
{

    const LOG_TYPE_INFO = LOG_TYPE_INFO;//info about program behaviour
    const LOG_TYPE_DEBUG = LOG_TYPE_DEBUG;//info useful for debugging or monitoring program state
    const LOG_TYPE_WARN = LOG_TYPE_WARN;//protentially harmful events or program states
    const LOG_TYPE_USER_ERROR = LOG_TYPE_USER_ERROR;//non fatal errors caused by user. eg handled exceptions
    const LOG_TYPE_APP_ERROR = LOG_TYPE_APP_ERROR;//non fatal errors caused by application 
    const LOG_TYPE_FATAL = LOG_TYPE_FATAL;//fatal errors that lead to program termination

    /**
     * Path where log file is stored.
     * @var type 
     */
    public $folder = 'runtime';
    /**
     * The base folder path where the logs will be saved
     * @var string
     */
    public $basename = '';
    /**
     * @var integer maximum filesize of log file. When this size is reached, it is moved into
     * archive and a new one started. Default is 2MB
     */
    public $set_log_filesize = 2097152; //2*1024*1024;

    /**
     * Writes informatio to application log
     * @param string $data data you want written
     * @param string $type
     * @return string log error message string
     */
    public function write($data, $type = self::LOG_TYPE_DEBUG)
    {
        
        $filename = $this->getLogFile();
        
        if($type==self::LOG_TYPE_APP_ERROR)
        {
            ob_start();
            debug_print_backtrace();
            $data.=ob_get_clean();
        }
        
        $message = sprintf('%s %s %s %s', date('Y-m-d H:i:s'),
            strtoupper($type), (string) $data , "\r\n");
        /** @var string $message */

        file_put_contents($filename, $message, FILE_APPEND);

        return $message;
    }

    /**
     * @param string $data of object with _toString() method
     */
    public function debug($data)
    {
        $this->write($data, self::LOG_TYPE_DEBUG);
    }
    
    public function fatal($data)
    {
        $this->write($data, self::LOG_TYPE_FATAL);
    }
    
    
    public function getLogFile()
    {
        $basename = (($this->basename) ? $this->basename : dirname(__FILE__)) . DS . $this->folder;
        $name = date('Y-m-d').'.log';
        $filename = $basename . DS . $name;
        if(file_exists($filename))
        {//check log size if greater than set limit
            if (filesize($filename) > $this->set_log_filesize)
            {
                //rename file and store
                $name = date('Hi'). '_' . $name;
                $newname = $basename . DS . $name;
                if(file_exists($newname)) {
                    do {
                        $newname = $basename . DS . rand().'_'.$name;
                    } while (file_exists($newname));
                }

                rename($filename, $newname);
            }
        }
        
        return $filename;
    }

}
