<?php
namespace Leo\Helpers\Formatter;
/**
 * Description of Boolean
 *
 * @author barnabasnanna
 * Date : 24/6/2016
 */
class Boolean implements I_Formatter
{
    public $value;
    public $trueValue = 'Yes';
    public $falseValue = 'No';
    
    public function format($value)
    {
        return !!$value ? lang($this->trueValue) : lang($this->falseValue);
    }
}
