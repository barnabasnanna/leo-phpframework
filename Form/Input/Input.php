<?php

namespace Leo\Form\Input;

use Exception;
use stdClass;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Input
 *
 * @author barnabasnanna
 */
abstract class Input implements InputInterface
{

    protected $id;
    protected $name;
    protected $value;
    protected $wrap;
    protected $label;
    protected $type = 'text';
    protected $options = [];
    protected $template = '';
    protected $hint;
    protected $model; //instance of Leo\MainModel
    protected $visible = true;
    protected $label_options = [];




    public function __construct($name, array $options = [])
    {
        $this->setName($name);
        $this->setOptions($options);
    }
    
    public function getLabelOptions()
    {
        $label_options = '';

        foreach ($this->label_options as $attr => $value)
        {
            $label_options.= " $attr = '$value' ";
        }

        return $label_options;
    }
    
    public function setLabelOptions(array $label_options)
    {
        $this->label_options = $label_options;
        return $this;
    }

    public function __toString()
    {
        try
        {
            return sprintf($this->getTemplate(), 
                    $this->getLabelOptions(),
                    $this->getId(), 
                    $this->getLabel(),
                    $this->getId(), 
                    $this->getName(),
                    $this->getOptions(),
                    $this->getValue(),
                    $this->getHint());
        }
        catch (Exception $ex)
        {
            return $ex->getMessage();
        }
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
    
    /**
     * @param bool $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getVisible()
    {
        return $this->visible;
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

    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    public function getId()
    {
        if (empty($this->id))
        {
            $this->setId($this->getName());
        }

        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    
    public function setName($name = '')
    {
        $this->name = $name;
        return $this;
    }

    public function setType($type = '')
    {
        $this->type = $type;
        return $this;
    }

    public function setValue($value = '')
    {
        $this->value = $value;
        return $this;
    }
    
    public function getValue()
    {
        return $this->value;
        
    }
    
    protected function getCleanName()
    {
        
        return is_null($this->getModel()) ? 
                    ucwords($this->clean($this->getName(), true))
                : $this->getModel()->getPropertyLabel($this->getName());
        
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
    
    public function getTemplate()
    {
        return $this->template;
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
    
    public function getHint()
    {
        return $this->hint;
    }

    public function setHint($hint)
    {
        $this->hint = $hint;
        return $this;
    }
    
    public function getModel()
    {
        return $this->model;
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    
}
