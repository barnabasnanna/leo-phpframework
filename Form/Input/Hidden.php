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
class Hidden extends Input
{

    protected $type = 'hidden';
    protected $template = '<input type="hidden" id="%s" name="%s" %s value="%s"/>';
    
    
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
    
    /**
     * Returns submited value else original value
     * @return string
     */
    public function getValue()
    {
        return leo()->getRequest()->getParam($this->getName(), parent::getValue());
    }
    
    public function __toString()
    {
        try
        {
            return sprintf($this->getTemplate(), 
                    $this->getId(), 
                    $this->getName(),
                    $this->getOptions(),
                    $this->getValue());
        }
        catch (\Exception $ex)
        {
            return $ex->getMessage();
        }
    }
    
        

}
