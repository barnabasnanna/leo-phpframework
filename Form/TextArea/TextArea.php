<?php

namespace Leo\Form\TextArea;

use Leo\Form\Input\Input;
use stdClass;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<?php

class TextArea extends Input
{

    protected $template = '<label for="%s">%s</label><textarea id="%s" name="%s" %s>%s</textarea>%s<br/>';

    public function __toString()
    {
        
        return sprintf($this->getTemplate(), 
                $this->getId(),
                $this->getLabel(),
                $this->getId(),
                $this->getName(), 
                $this->getOptions(), 
                $this->getValue(),
            $this->getHint());
    }

    /**
     * Returns submited value else original value
     * @return string
     */
    public function getValue()
    {
        return leo()->getRequest()->getParam($this->getName(), parent::getValue());
    }

    public function getId()
    {
        if (empty($this->id))
        {
            $this->setId($this->getName());
        }

        return $this->id;
    }

    public function validate()
    {
        
    }

    public function rules()
    {
        
    }

}
