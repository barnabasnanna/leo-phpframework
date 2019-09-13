<?php

namespace Leo\Form\Input;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Text
 *
 * @author barnabasnanna
 */
class Submit extends Input
{
    protected $label_options = ['class'=>'sr-only'];

    protected $type = 'submit';
    protected $template = '<label %s for="%s">%s</label> <input type="submit" id="%s" name="%s" %s value="%s"/>%s<br/>';

        
    public function getValue()
    {
        if (empty($this->value))
        {
            $this->setValue($this->getCleanName());
        }

        return $this->value;
    }
    
    /**
     * Validation rule
     */
    public function rules()
    {
        
    }

    /**
     * Runs validation of this component
     */
    public function validate()
    {
        return true;
    }
    
    

}
