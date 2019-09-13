<?php

namespace Leo\Form\DropDown;

use app\Packages\DataSets\Models\Attribute;
use Leo\ObjectBase;
use stdClass;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Select
 *
 * @author barnabasnanna
 */
class Select extends ObjectBase
{

    protected $id;
    protected $name;
    protected $wrap;
    protected $value;
    protected $label;
    protected $template = '<label %s for="%s">%s</label><select id="%s" name="%s" %s>%s</select>%s<br/>';
    protected $dropDownOptions = [];
    protected $options = [];
    protected $hint;
    protected $options_separator = '|';
    protected $visible = true;
    protected $label_options =[];
    /**
     * Regex string used to match input names
     * eg '/^([a-zA-Z]+)\[([0-9])\](\[\])$/' to match category[2][]
     * @var null
     */
    protected $name_matching_pattern = null;
    
    public function getId()
    {
        if (empty($this->id))
        {
            $this->setId($this->getName());
        }

        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns submitted value else original value.
     * An array if multiple option set
     * @return mixed
     */
    public function getValue()
    {
        return leo()->getRequest()->getParam(
            preg_replace('/[^a-zA-Z0-9_-]/', '', $this->getName()),
            $this->value);
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

    public function getDropDownOptions()
    {
        $options = '<option value="">' . lang('--Select--') . '</option>';

        foreach ($this->dropDownOptions as $key => $value)
        {
            $isSelected = false;

            //matches
            if($this->getNameMatchingPattern() && preg_match($this->getNameMatchingPattern(),$this->getName(),$matches))
            {
                $_requestValue = leo()->getRequest()->getParam($matches[1]);

                if(is_array($_requestValue) && isset($_requestValue[$matches[2]]))
                {
                    $_requestValue = $_requestValue[$matches[2]];

                    if (in_array(strval($key), array_map('strval', $_requestValue))) {
                        $isSelected = true;
                    }
                }

            }

            if($isSelected===false)
            {
                //If multiple option
                if(is_array($this->getValue()))
                {
                    if (in_array(strval($key), array_map('strval', $this->getValue()))) {
                       $isSelected = true;
                    }
                }
                else
                {
                    $isSelected = strval($key) === (string)$this->getValue();
                }
            }

            $options .= '<option value="' . trim($key) . '" ' . ($isSelected ? 'selected="selected"' : '') . '>' . trim($value) . '</option>';

        }
        
        
        return $options;
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

    public function getHint()
    {
        return $this->hint;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param array $dropDownOptions
     * @return Select
     */
    public function setDropDownOptions(array $dropDownOptions)
    {
        $this->dropDownOptions = $dropDownOptions;
        return $this;
    }
    
    public function getOptionsSeparator()
    {
        return $this->options_separator;
    }
    
    public function setOptionsSeparator($separator)
    {
        return $this->options_separator = $separator;
    }

    /**
     * @param $attribute_options_string
     * @return Select
     */
    public function setDropDownOptionsFromString($attribute_options_string)
    {

        $options = explode($this->getOptionsSeparator(), trim($attribute_options_string, $this->getOptionsSeparator()));

        $dropDownOptions = [];

        foreach ($options as $option_string)
        {
            $option_key_value = explode('::', $option_string);

            $value = isset($option_key_value[1]) ? $option_key_value[1] : $option_key_value[0];
            
            $dropDownOptions[$option_key_value[0]] = $value;

        }
        
        $this->setDropDownOptions($dropDownOptions);
        
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

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function setHint($hint)
    {
        $this->hint = $hint;
        return $this;
    }

    public function __construct($name, array $options=[])
    {
        parent::__construct();
        $this->setName($name);
        $this->setOptions($options);
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function __toString()
    {
        return sprintf($this->getTemplate(), 
                $this->getLabelOptions(),
                $this->getId(), $this->getLabel(), 
                $this->getId(), $this->getName(), 
                $this->getOptions(), 
                $this->getDropDownOptions(),
                $this->getHint() ? 'Hint:- '.$this->getHint() : '');
    }

    public function rules()
    {
        
    }

    public function validate()
    {
        
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

        foreach ($options as $key => $value)
        {
            $options_string.= " $key = '$value' ";
        }

        $this->wrap->options_string = $options_string;

        $wrap_template = '<%s %s>%s</%s>';

        $new_template = sprintf($wrap_template, $this->wrap->container, $this->wrap->options_string, $this->getTemplate(), $this->wrap->container);

        $this->setTemplate($new_template);
    }
    
    /**
     * Runs validation of this attribute
     * @param Attribute $attribute Attribute to be vaidated
     * @return bool|array Validation result. True if it passes else error message string
     */
    public static function validateAttribute(Attribute $attribute)
    {
        $submitted_value = leo()->getRequest()->getParam($attribute->getAttributeCleanName());
        if($attribute->isRequired() && empty($submitted_value))
        {
            return sprintf('%s is required',$attribute->getAttributeName());
        }
        
        return true;
    }
    
    /**
     * @param bool $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }
    
    /**
     * @return boolean
     */
    public function getVisible()
    {
        return $this->visible;
    }
    
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
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

    /**
     * @return null
     */
    public function getNameMatchingPattern()
    {
        return $this->name_matching_pattern;
    }

    /**
     * @param null $name_matching_pattern
     * @return Select
     */
    public function setNameMatchingPattern($name_matching_pattern)
    {
        $this->name_matching_pattern = $name_matching_pattern;
        return $this;
    }
}
