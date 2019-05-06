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
class Text extends Input
{

    protected $type = 'text';
    protected $template = '<label %s for="%s">%s</label> <input type="text" id="%s" name="%s" %s value="%s"/>%s<br/>';
   
    
    /**
     * Returns submited value else original value
     * @return string
     */
    public function getValue()
    {
        return leo()->getRequest()->getParam($this->getName(), parent::getValue());
    }

    

}
