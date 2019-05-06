<?php
namespace Leo\Interfaces;

/**
 * Interface for all logging classes
 * @author Barnabas
 */
interface Logger
{
    /**
     * Writes log information to the log file
     * @param string $data the data you want written to log
     * @param string $type data log type
     */
    public function write($data, $type);
    /**
     * Return log filepath
     */
    public function getLogFile();
}
