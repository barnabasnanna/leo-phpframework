<?php
namespace Leo\Helpers\Formatter;

/**
 * Ensures class implements the format method
 *
 * @author barnabasnanna
 */
interface I_Formatter
{
    /**
     *
     * @param mixed $value
     * @return
     * @internal param array|string $config
     */
    public function format($value);
    
}
