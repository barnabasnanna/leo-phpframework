<?php

namespace Leo\Helpers\Formatter;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DateTime
 *
 * @author barnabasnanna
 */
class DateTime implements I_Formatter
{

    public $format = 'M d, Y';

    public function format($value)
    {
        try
        {
            if(is_numeric($value))
                return (new \DateTime())->setTimestamp($value)->format($this->format);
            else
              return (new \DateTime($value))->format($this->format);
        }
        catch (\Exception $e)
        {
            return $e->getMessage();
        }
        
    }

}
