<?php

namespace Leo;

use Exception;
use Leo\Helpers\Validator;

/**
 * MainModel is the base class for all ActiveRecord models and form models.
 * Gives access to validator and Db activerecord
 *
 * @author Barnabas
 */
abstract class MainModel extends ObjectBase
{

    protected $_validator_ = null;

    /**
     * Mode model is on.
     * @var string 
     */
    protected $_mode_ = '';

    /**
     *
     * @return boolean true if validation passed
     * @throws Exception
     */
    public function validate()
    {

        if ($this->beforeValidate())
        {
            $this->gv()->run();
        }

        $this->afterValidate();

        return !$this->hasErrors();
    }

    protected function beforeValidate()
    {
        return true;
    }

    protected function afterValidate()
    {
        
    }

    /**
     * Return validator class
     * @return Validator
     * @throws Exception
     */
    protected function gv()
    {

        if (!$this->_validator_ instanceof Validator)
        {
            $this->_validator_ = Leo::lc('validator', array('object'=>clone $this));
        }

        return $this->_validator_;
    }

    public function getMode()
    {
        return $this->_mode_;
    }

    /**
     * Sets the mode the attribute is running on which 
     * is used by validators identify with rules to run
     * @param string $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->_mode_ = $mode;
        return $this;
    }

    /**
     * Validation errors. Array of arrays
     * array(
        array( 'property' => 'error')
     *)
     * @return array
     */
    public function getErrors()
    {
        return $this->gv()->getErrorMessage();
    }

    public function setErrors(array $errors)
    {
        $this->gv()->addErrors($errors);
    }

    /**
     * Returns true if the model has errors after validation. False otherwise
     * @return bool
     * @throws Exception
     */
    public function hasErrors()
    {
        return count($this->getErrors());
    }

    public function getError($property)
    {
        if ($this->hasError($property))
        {
            $errors = $this->getErrors();
            return $errors[$property];
        }
    }

    public function hasError($property)
    {
        return array_key_exists($property, $this->getErrors());
    }

    /**
     * Return an array with the
     * passed object properties as keys and their values as values
     *
     * <pre>
     * $this->getProperties(['firstname', 'lastname']);
     *
     * return array(
     *  'firstname'=>'John',
     *  'lastname'=>'Pual'
     * )
     * </pre>
     * @param array $object_properties
     * @param bool $throwException should and excepetion be throw is not a property
     * @return array
     * @throws Exception
     */
    public function getProperties(array $object_properties, $throwException = true)
    {
        $properties_values = array();
        
        foreach ($object_properties as $property)
        {
            $properties_values[$property] = $this->getPropertyValue($property,$throwException);
        }

        return $properties_values;
    }

    /**
     * Returns the value of a property if it exist
     * @param string $property
     * @param bool $throwException
     * @return mixed
     * @throws Exception if the property does not exist
     */
    public function getPropertyValue($property,$throwException = true)
    {
        if ($this->hasProperty($property))
        {
            return $this->{'get' . $property}();
        }

        if($throwException)
        {
            throw new Exception(sprintf('Property %s::%s does not exist', get_called_class(), $property));
        }
    }

    /**
     * Set the model properties with passed array. Array keys are the model's properties and
     * array values are their corresponding values
     * @param array $model_property_values
     * @param bool $throwException should exception be thrown if property not found
     * @throws Exception
     */
    public function setProperties(array $model_property_values, $throwException = true)
    {

        foreach ($model_property_values as $property => $value)
        {
            $this->setProperty($property, $value, $throwException);
        }
    }

    /**
     * Set a model's property
     * @param string $property property name
     * @param string $value property value
     * @param bool $throwException should an exception be thrown if property does not exist
     * @throws Exception
     */
    public function setProperty($property, $value, $throwException = true)
    {

        if ($this->hasProperty($property))
        {
            $this->{'set' . $property }($value);
        }
        elseif($throwException)
        {
            /**
             * TODO Change this to a custom exception like MissingPropertyException
             * Which can therefore be caught and handled
             */
            throw new Exception(
            sprintf('Property %s::%s does not exist', get_called_class(), $property)
            );

        }
    }

    /**
     * An object has a property if the property exist or set method exists
     * @param string $property
     * @return boolean true if property exists
     */
    public function hasProperty($property)
    {
        $exists = false;
        

        if (method_exists($this, 'set' . clean($property)) OR method_exists($this, 'set' . $property))
        {
            $exists = true;
        }
        elseif (property_exists($this, $property))
        {
            $exists = true;
        }

        return $exists;
    }

    /**
     * Validation rules for the object
     * <p>array( array('properties'), 'callable', array('modeON', '_off_'=>'modeOFF'), array() )</p>
     * @return array
     */
    public function rules()
    {
        return array();
    }

    /**
     * Return the display name of the property
     * @param $property
     * @return mixed|string
     */
    public function getPropertyLabel($property)
    {
        if(\method_exists($this, 'getPropertyLabels'))
        {
            $labels = $this->getPropertyLabels();
            if(isset($labels[$property]))
            {
                return $labels[$property];
            }
        }
        
        return ucwords(preg_replace('/[^a-zA-Z0-9]/',' ', $property));
        
    }
    
    /**
     * Returns the friendly display name of the model's properties.
     * Use the property names as keys and their display names as values
     * <pre>
     * return array('property_name'=>'Property Name');
     * </pre>
     * @return array
     */
    public function getPropertyLabels()
    {
       return array();
    }



}
