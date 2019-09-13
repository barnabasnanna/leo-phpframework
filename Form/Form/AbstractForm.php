<?php

namespace Leo\Form\Form;

use Leo\Db\ActiveRecord;
use Leo\Form\DropDown\Select;
use Leo\Form\DropDown\Date;
use Leo\Form\Input\AnyInput;
use Leo\Form\Input\Hidden;
use Leo\Form\Input\Submit;
use Leo\Form\Input\Text;
use Leo\Form\TextArea\TextArea;
use Exception;
use Leo\ObjectBase;

/**
 * Description of Form
 *
 * @author barnabasnanna
 */
abstract class AbstractForm extends ObjectBase
{

    protected $hint;
    protected $form_body = null;
    protected $dataset_id;
    protected $class = '';
    protected $elements = array();
    protected $type; //horizontal or vertical mode
    protected $token;
    protected $options = '';
    protected $action;
    protected $method = 'post'; //post or get
    protected $deliveryType = 'normal'; //ajax or not
    protected $clientValidation = false; //true or false. Should the form validate in the client
    protected $form_errors = [];
    
    public function __construct(array $attr = [])
    {
        parent::__construct();
        foreach ($attr as $attr_name => $attr_value)
        {
            if (property_exists($this, $attr_name))
            {
                $this->{'set' . $attr_name}($attr_value);
            }
            else
            {
                throw new Exception($attr_name . lang(' is an unknown property'));
            }
        }
    }
    
    /**
     * Does the form have errors
     * @return bool true if form attributes have not errors else false
     */
    public function hasErrors()
    {
        return !!count($this->getFormErrors());
    }
    
    public function getFormErrors()
    {
        return $this->form_errors;
    }
    
    public function setFormErrors(array $attribute_errors)
    {
        $this->form_errors = $attribute_errors;
        return $this;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    

    /**
     * Returns the other form attributes
     * @return string
     */
    public function getOptions()
    {
        $options = ['action', 'method', 'class'];

        foreach ($options as $attr)
        {
            $this->options.= " $attr = '" . $this->{'get' . $attr}() . "'";
        }

        return $this->options;
    }

    public function getDatasetId()
    {
        return $this->dataset_id;
    }

    public function setDatasetId($dataset_id)
    {
        $this->dataset_id = $dataset_id;
        return $this;
    }

    public function getHint()
    {
        return $this->hint;
    }

    public function setHint($hint)
    {
        $this->hint = $hint;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getMehtod()
    {
        return $this->method;
    }

    public function getDeliveryType()
    {
        return $this->deliveryType;
    }

    public function getClientValidation()
    {
        return $this->clientValidation;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setMehtod($mehtod)
    {
        $this->method = $mehtod;
        return $this;
    }

    public function setDeliveryType($deliveryType)
    {
        $this->deliveryType = $deliveryType;
        return $this;
    }

    public function setClientValidation($clientValidation)
    {
        $this->clientValidation = $clientValidation;
        return $this;
    }

    //TODO Ensure the right element are added using Interface
    protected function addElement($attr)
    {
        $this->elements[] = $attr;
    }

    /**
     * 
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Add form elements to form
     */
    public function setElements(array $elements)
    {
        foreach ($elements as $element)
        {
            if (method_exists($element, '__toString'))
            {
                $this->addElement($element);
            }
        }
    }
    
    /**
     * Render notification messages after form submission
     * @return string notification error
     */
    public function renderFormNotifcation()
    {
        if(!$this->isFormSubmitted())
        {
             $notification_string = '';
        }
        elseif($this->hasErrors())
        {
            $notification_string ='';
            
            foreach($this->getFormErrors() as $attribute_errors)
            {
                foreach ($attribute_errors as $errorString)
                {
                    $notification_string.=$errorString.'<br />';
                }
            }
            
            $notification_string = '<div class="alert alert-danger">'.$notification_string.'</div>';
        }
        else
        {
            $notification_string = '<div class="alert alert-success">'.lang('Success').'</div>';
        }
        
        return $notification_string;
    }

    /**
     * Render all the attributes
     * @return string form body
     */
    public function renderElements()
    {
        if($this->form_body === null){
            $this->form_body = '';
            foreach ($this->getElements() as $element) {
                if (is_object($element)) {
                    if (!$element->getVisible()) {
                        continue;
                    }
                    $this->form_body .= $element;//calls the __toString
                }
            }
        }

        return $this->form_body;
    }

    /**
     * 
     * @param string $name
     * @param array $options
     * @return Select
     */
    public function select($name, array $options = [])
    {
        $select = new Select($name,$options);
        $this->addElement($select);
        return $select;
    }
    
    /**
     * 
     * @param string $name
     * @param array $options
     * @return Date
     */
    public function date($name, array $options=[])
    {
        $date = new Date($name,$options);
        $this->addElement($date);
        return $date;        
    }

    /**
     * Add input text element
     * @param string $name element name
     * @param array $options
     * @return Text
     */
    public function text($name, array $options = [])
    {

        $text = new Text($name, $options);
        $this->addElement($text);
        return $text;
    }
    
    /**
     * Adds an input element. Can be used to add custom input types
     * @param string $type element type
     * @param string $name element name
     * @param array $options
     * @return AnyInput
     */
    public function any($type, $name, array $options = [])
    {

        $anyInput = (new AnyInput($name, $options))->setType($type);
        $this->addElement($anyInput);
        return $anyInput;
    }

    /**
     * Add input text element
     * @param string $name element name
     * @param array $options
     * @return Hidden
     */
    public function hidden($name, array $options = [])
    {

        $hidden = new Hidden($name, $options);
        $this->addElement($hidden);
        return $hidden;
    }

    /**
     * Add a input submit button
     * @param string $name element name
     * @param array $options
     * @return Submit
     */
    public function submit($name, array $options = [])
    {

        $submit = new Submit($name, $options);
        $this->addElement($submit);
        return $submit;
    }

    /**
     * Add a text area to form
     * @param string $name element name
     * @param array $options
     * @return TextArea
     */
    public function textArea($name, array $options = [])
    {
        $textArea = new TextArea($name, $options);
        $this->addElement($textArea);
        return $textArea;
    }
    
    /**
     * Is the form submitted
     * @return boolean
    */
    public function isFormSubmitted()
    {
        return leo()->getRequest()->getParam('dataset_id') ? true : false;
        
    }

    /**
     * @return null
     */
    public function getFormBody()
    {
        return $this->form_body;
    }

    /**
     * @param null $form_body
     * @return AbstractForm
     */
    public function setFormBody($form_body)
    {
        $this->form_body = $form_body;
        return $this;
    }


}
