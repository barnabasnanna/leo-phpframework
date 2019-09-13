<?php
namespace Leo\Helpers\Formatter;

/**
 * Description of Currency
 *
 * @author barnabasnanna
 */
class Currency implements I_Formatter
{
    public $symbol = '&pound;';
    
    
    public function format($value)
    {
        return $this->symbol.number_format($value,2);
    }
}
