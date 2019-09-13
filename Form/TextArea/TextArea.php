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

    protected $hint;
    protected $value;
    protected $rows;
    protected $cols;
    protected $id;
    protected $name;
    protected $options = [];
    protected $label;
    protected $wrap;
    protected $template = '<label for="%s">%s</label><textarea id="%s" name="%s" %s>%s</textarea>%s<br/>';

    /**
     * Adds required option to form element
     * @param bool $required
     * @return $this
     */
    public function setRequired($required)
    {
        if($required)
        {
            $options = array_merge($this->options, ['required'=>'required']);
            $this->setOptions($options);
        }
        
        return $this;
        
    }
    
    public function __construct($name, array $options = [])
    {
        $this->setName($name);
        $this->setOptions($options);
    }

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
    
    public function getHint()
    {
        return $this->hint;
    }
    
    public function setHint($hint)
    {
        $this->hint = $hint;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the other input attributes
     * @return string
     */
    public function getOptions()
    {
        $options = '';

        foreach ($this->options as $attr => $value)
        {
            $options.= " $attr = '$value' ";
        }

        return $options;
    }

    public function setName($name='')
    {
        $this->name = $name;
        return $this;
    }

    protected function getCleanName()
    {
        return ucwords($this->clean($this->getName(), true));
    }

    public function getLabel()
    {
        if (empty($this->label))
        {
            $this->setLabel($this->getCleanName());
        }

        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Returns submited value else original value
     * @return string
     */
    public function getValue()
    {
        return leo()->getRequest()->getParam($this->getName(), parent::getValue());
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function getCols()
    {
        return $this->cols;
    }

    public function getId()
    {
        if (empty($this->id))
        {
            $this->setId($this->getName());
        }

        return $this->id;
    }

    public function setValue($value='')
    {
        $this->value = $value;
        return $this;
    }

    public function setRows($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    public function setCols($cols)
    {
        $this->cols = $cols;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * replaces all non alpha numeric characters in string to space
     * @param string $str string you want cleaned
     * @param boolean $lowerCase set to true if you want a lowercased version returned
     * @return string sanitized string
     */
    protected function clean($str = '', $lowerCase = false)
    {
        $s = preg_replace('/[^A-Za-z0-9]/', ' ', $str);
        return $lowerCase ? strtolower($s) : $s;
    }

    public function validate()
    {
        
    }

    public function rules()
    {
        
    }
    
    public function getWrap()
    {
        return $this->wrap;
    }

    public function wrap($container = 'div', array $options = [])
    {
        $this->wrap = new stdClass();
        $this->wrap->container = $container;
        $this->wrap->options = $options;
        $options_string = '';
        
        foreach($options as $key=>$value)
        {
            $options_string.= " $key = '$value' ";
        }
        
        $this->wrap->options_string = $options_string;
        
        $wrap_template = '<%s %s>%s</%s>';
        
        $new_template = sprintf($wrap_template, $this->wrap->container, $this->wrap->options_string, 
                $this->getTemplate(), $this->wrap->container);
        
        $this->setTemplate($new_template);
    }
    
    

}
