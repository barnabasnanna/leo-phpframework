<?php

namespace Leo\Form\Input;


/**
 * Can be any input type
 *
 * @author barnabasnanna
 */
class AnyInput extends Input
{

    protected $type = 'text';
    protected $template = '<label for="%s" %s>%s</label> <input type="%s" id="%s" name="%s" %s value="%s"/>%s';
    
    public function __toString()
    {
        try
        {
            return sprintf($this->getTemplate(), 
                    $this->getId(),
                    $this->getLabelOptions(),
                    $this->getLabel(),
                    $this->getType(),
                    $this->getId(), 
                    $this->getName(),
                    $this->getOptions(),
                    $this->getValue(),
                    $this->getHint());
        }
        catch (\Exception $ex)
        {
            return $ex->getMessage();
        }
    }
        
    /**
     * Returns submited value else original value
     * @return string
     */
    public function getValue()
    {
        return leo()->getRequest()->getParam($this->getName(), parent::getValue());
    }

    public function rules()
    {
        
    }

    public function validate()
    {
        
    }

}
