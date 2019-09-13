<?php

namespace Leo\Helpers;

use Exception;
use Leo\Form\File\Validator\singleFileUploadValidator;
use Leo\MainModel;

class Validator
{

    protected $object = null;
    protected $mode = null;
    protected $errorMessage = array();
      
    public function getMode()
    {
        return $this->getObject()->getMode();
    }

    public function setMode($mode)
    {
        
        $this->mode = $mode;
        return $this;
    }

    public function setObject($obj)
    {
        $this->object = $obj;
    }

    /**
     * 
     * @return MainModel
     */
    public function getObject()
    {
        return $this->object;
    }

    public function getRules()
    {
        return $this->getObject()->rules();
    }

    public function run()
    {

        foreach ($this->getRules() as $rule)
        {

            $properties = ( isset($rule[0]) && is_array($rule[0]) ) ? $rule[0] : array();
            $callable = $rule[1];
            $modes = (isset($rule[2]) && is_array($rule[2])) ? $rule[2] : array();
            $other = (isset($rule[3]) && is_array($rule[3])) ? $rule[3] : array();
                                    
            if (!$this->runRuleCheck($modes))
            {
                continue;
            }

            foreach ($properties as $property)
            {
                if (!property_exists($this->getObject(), $property))
                {
                    throw new Exception($property . ' is not a property of '.get_class($this->getObject()));
                }
                
                $this->runValidation($property, $callable, $other);
            }
        }
    }

    /**
     * Check if rule should be run based on object's mode and rule modes
     * @param array $modes
     * @return boolean
     */
    public function runRuleCheck(array $modes = array())
    {
        
        if (empty($modes))
            return true; //if no modes . always run rule

            
        //dont run if object's mode is inside _off_ array
        if (isset($modes['_off_']) && in_array($this->getMode(), explode(',', $modes['_off_'])))
            return false;

        //run if mode is in rule modes
        if (in_array($this->getMode(), $modes))
            return true;
        
        return false;
    }

    public function runValidation($property, $callable, $other)
    {

        if (is_string($callable))
        {
            foreach (explode(',', $callable) as $methodname)
            {
                $methodname = trim($methodname);

                if (method_exists($this->getObject(), $methodname))
                {
                    $callable_response = call_user_func_array(
                            array($this->getObject(), $methodname), 
                            array($property, $other));
                }
                elseif (in_array($methodname, array_keys($this->getValidationMethods())))
                {
                    $callable_response = call_user_func_array(array($this, $methodname), array($property, $other));
                }
                else
                {
                    throw new Exception('Callable ' . $methodname . ' not found.');
                }
                
                $this->handleResponse($property, $callable_response);
            }
        }
        elseif (is_array($callable) && is_callable($callable))
        {
            $callable_response = call_user_func_array($callable, array($property, $other));
            $this->handleResponse($property, $callable_response);
        }
        else
        {
            throw new Exception('Callable ' . $callable . ' not found');
        }
    }

    /**
     * Handles the responses from the validation methods.
     * If not TRUE, adds the error message returned
     * @param string $property
     * @param mixed $callable_response
     */
    protected function handleResponse($property, $callable_response)
    {
        if ($callable_response !== TRUE)
        {
            $this->addError($property, $callable_response);
        }
        
    }

    public function addError($property, $error)
    {
        if (!array_key_exists($property, $this->getErrorMessage()))
        {
            $this->errorMessage[$property] = array();
        }

        is_array($error) ?
                        $this->errorMessage[$property] = $error :
                        $this->errorMessage[$property][] = $error;
    }

    public function addErrors(array $property_errors)
    {
        foreach ($property_errors as $property => $error)
        {
            if ($this->getObject()->hasProperty($property))
            {
                $this->addError($property, $error);
            }
        }
    }

    public function email($property, $other)
    {

       $property_value = $this->getObject()->{'get' . $property}();

        //if null is allowed return true
        if(empty($property_value) && isset($other['allowNull']) && boolval($other['allowNull']))
        {
            return true;
        }

        if (FALSE !== \filter_var($property_value, FILTER_VALIDATE_EMAIL))
        {
            return true;
        }

        $validation_methods = $this->getValidationMethods();

        if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
        {
            $template = $validation_methods[__FUNCTION__]['errorMessage'];

            return sprintf($template, $this->getLabel($property));
        }

        return false;
    }

    public function required($property, $other)
    {
        $property_value = $this->getObject()->{'get' . $property}();

        if (!empty($property_value) OR is_numeric($property_value))
        {
            return true;
        }

        $validation_methods = $this->getValidationMethods();

        if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
        {
            $template = $validation_methods[__FUNCTION__]['errorMessage'];

            return sprintf($template, $this->getLabel($property));
        }

        return false;
    }
    
    /**
     * Get the display name of the property
     * @param string $property
     * @return string
     */
    protected function getLabel($property)
    {
        return $this->getObject()->getPropertyLabel($property);
    }

    /**
     * @param $property
     * @param $other
     * @return bool|string
     * @throws Exception
     */
    public function maxLength($property, $other)
    {
        if (!isset($other['max']))
        {
            throw new \Exception('Maximum length not given');
        }

        $property_value = $this->getObject()->{'get' . $property}();

        //if null is allowed return true
        if(empty($property_value) && isset($other['allowNull']) && boolval($other['allowNull']))
        {
            return true;
        }

        if ($other['max'] < strlen($property_value))
        {
            $validation_methods = $this->getValidationMethods();

            if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
            {
                $template = $validation_methods[__FUNCTION__]['errorMessage'];

                return sprintf($template, $this->getLabel($property), $other['max']);
            }

            return false;
        }

        return true;
    }

    /**
     * Minimum value allowed for property
     * @param $property
     * @param $other
     * @return bool|string
     * @throws Exception
     */
    public function minValue($property, $other)
    {
        if (!isset($other['min_value']))
        {
            throw new \Exception('Minimum value not given');
        }

        $property_value = $this->getObject()->{'get' . $property}();

        //if null is allowed return true
        if(empty($property_value) && isset($other['allowNull']) && boolval($other['allowNull']))
        {
            return true;
        }

        if ($other['min_value'] > $property_value)
        {
            $validation_methods = $this->getValidationMethods();

            if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
            {
                $template = $validation_methods[__FUNCTION__]['errorMessage'];

                return sprintf($template, $this->getLabel($property), $other['min_value']);
            }

            return false;
        }

        return true;
    }

    /**
     * Maximum value allowed for property
     * @param $property
     * @param $other
     * @return bool|string
     * @throws Exception
     */
    public function maxValue($property, $other)
    {
        if (!isset($other['max_value']))
        {
            throw new \Exception('Maximum value not given');
        }

        $property_value = $this->getObject()->{'get' . $property}();

        //if null is allowed return true
        if(empty($property_value) && isset($other['allowNull']) && boolval($other['allowNull']))
        {
            return true;
        }

        if ($other['max_value'] < $property_value)
        {
            $validation_methods = $this->getValidationMethods();

            if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
            {
                $template = $validation_methods[__FUNCTION__]['errorMessage'];

                return sprintf($template, $this->getLabel($property), $other['max_value']);
            }

            return false;
        }

        return true;
    }

    /**
     * Minimum length allowed for property value
     * @param $property
     * @param $other
     * @return bool|string
     * @throws Exception
     */
    public function minLength($property, $other)
    {
        if (!isset($other['min']))
        {
            throw new \Exception('Minimum length not given');
        }

        $property_value = $this->getObject()->getPropertyValue($property);

        //if null is allowed return true
        if(empty($property_value) && isset($other['allowNull']) && boolval($other['allowNull']))
        {
            return true;
        }

        if ($other['min'] > strlen($this->getObject()->getPropertyValue($property)))
        {
            $validation_methods = $this->getValidationMethods();

            if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
            {
                $template = $validation_methods[__FUNCTION__]['errorMessage'];

                return sprintf($template, $this->getLabel($property), $other['min']);
            }

            return false;
        }

        return true;
    }

    /**
     * Is property value a numeric strin
     * @param $property
     * @param $other
     * @return bool|string
     */
    public function numeric($property, $other)
    {
        $property_value = $this->getObject()->getPropertyValue($property);

        //if null is allowed return true
        if(empty($property_value) && isset($other['allowNull']) && boolval($other['allowNull']))
        {
            return true;
        }

        if(!is_numeric($property_value))
        {
            $validation_methods = $this->getValidationMethods();

            if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
            {
                $template = $validation_methods[__FUNCTION__]['errorMessage'];

                return sprintf($template, $this->getLabel($property));
            }

            return false;
        }
        
        return true;
    }

    /**
     * Is property value an alpha numeric
     * @param $property
     * @param $other
     * @return bool|string
     */
    public function alphanum($property, $other)
    {
        $property_value = $this->getObject()->getPropertyValue($property);

        //if null is allowed return true
        if(empty($property_value) && isset($other['allowNull']) && boolval($other['allowNull']))
        {
            return true;
        }

        if(!ctype_alnum($property_value))
        {
            $validation_methods = $this->getValidationMethods();

            if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
            {
                $template = $validation_methods[__FUNCTION__]['errorMessage'];

                return sprintf($template, $this->getLabel($property));
            }

            return false;
        }

        return true;
    }

    /**
     * Is property value an integer
     * @param $property
     * @param $other
     * @return bool|string
     */
    public function isint($property, $other)
    {
        $property_value = $this->getObject()->getPropertyValue($property);

        //if null is allowed return true
        if(empty($property_value) && isset($other['allowNull']) && boolval($other['allowNull']))
        {
            return true;
        }
        
        if(!is_integer($property_value) && !ctype_digit($property_value))
        {
            $validation_methods = $this->getValidationMethods();

            if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
            {
                $template = $validation_methods[__FUNCTION__]['errorMessage'];

                return sprintf($template, $this->getLabel($property));
            }

            return false;
        }
        
        return true;
    }
    

    /**
     * Ensures a value doesnt already exist in a database table column
     * @param string $property
     * @param array $other
     * @return boolean
     * @throws Exception
     */
    public function unique($property, $other)
    {

        $table_column = $property;

        if (!isset($other['table']))
        {
            throw new \Exception('Table name not provided in rule other array');
        }

        if (isset($other['table_column']))
        {
            $table_column = $other['table_column'];
        }
                
        $db = leo()->getDb();

        $property_value = $this->getObject()->getPropertyValue($property);
        
        $whereCondition = array($table_column => $property_value);

        //if other conditions exists. Add them to query
        if(!empty($other['AND']) && is_array($other['AND']))
        {
            $column_names = $other['AND'];

            foreach ($column_names as $column_name => $value)
            {
                if(is_numeric($column_name))
                {
                    $column_name = $value;

                    if($this->getObject() instanceof MainModel)
                    {
                        $value = $this->getObject()->getPropertyValue(trim($column_name));
                    }
                }

                $whereCondition[$column_name] = $value;
            }
        }

        $column_params = $db->getPdoFormat(
                $whereCondition
        );


        $query = $db->table($other['table'])->select(array("COUNT($table_column) as count"));
        
        //create where conditional of sql statement
        foreach($column_params['columns'] as $column_name=>$placeholder)
        {
            $column_name = str_replace('`','',$column_name);

            if(isset($other['OPERATOR']) && isset($other['OPERATOR'][$column_name]))
            {
                $query->where([$column_name, $placeholder, $other['OPERATOR'][$column_name]], [$placeholder => $column_params['params'][$placeholder]]);
            }
            else
            {
                $query->where([$column_name,$placeholder], [$placeholder=>$column_params['params'][$placeholder]]);
            }
        }

        if ($query->run()->getFirst())
        {//already exists
            $validation_methods = $this->getValidationMethods();

            if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
            {
                $template = $validation_methods[__FUNCTION__]['errorMessage'];

                return sprintf($template, $property_value, $other['table'], $property);
            }

            return false;
        }

        return true;
    }

    /**
     * Single file validation
     * @param $property
     * @param $other
     * @return bool|string
     */
    public function file($property, $other = [])
    {
        $errorMessage ='';

        if( ( !isset($_FILES[$property]) OR empty($_FILES[$property]['name']))

            && (isset($other['allowNull']) && boolval($other['allowNull'])))
        {
            return true;

        }elseif(isset($_FILES[$property])) {

            $validator = new singleFileUploadValidator();

            if (!$validator->checkFiles(array_merge(['inputName' => $property],$other))) {

                foreach($validator->getFileErrors() as $error)
                {
                    $errorMessage .= implode(',', $error);
                }

                return $errorMessage;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return $this->getObject()->getPropertyLabel($property) . ' is not set';
        }


    }

    /**
     * Ensures a value already exist in a database table column
     * @param string $property
     * @param array $other
     * @return boolean
     * @throws Exception
     */
    public function exists($property, $other)
    {

        $table_column = $property;

        if (!isset($other['table']))
        {
            throw new \Exception('Table not provided on other parameters');
        }

        if (isset($other['column']) || isset($other['table_column']))
        {
            $table_column = isset($other['column']) ? $other['column'] : $other['table_column'];
        }

        $db = leo()->getDb();

        $property_value = $this->getObject()->getPropertyValue($property);

        //if null is allowed return true
        if(empty($property_value) && isset($other['allowNull']) && boolval($other['allowNull']))
        {
            return true;
        }
        elseif(!empty($property_value))
        {
            $whereCondition = array($table_column => $property_value);

            //if other conditions exists. Add them to query
            if(!empty($other['AND']) && is_array($other['AND']))
            {
                $column_names = $other['AND'];

                foreach ($column_names as $column_name => $value)
                {
                    if(is_numeric($column_name))
                    {
                        $column_name = $value;
                        $value = $this->getObject()->getPropertyValue(trim($column_name));
                    }

                    $whereCondition[$column_name] = $value;
                }
            }

            $column_params = $db->getPdoFormat(
                $whereCondition
            );

            $query = $db->table($other['table'])->select(array("COUNT($table_column) as count"));

            //create where conditional of sql statement
            foreach($column_params['columns'] as $column_name=>$placeholder)
            {
                $column_name = str_replace('`','',$column_name);
                if(isset($other['OPERATOR']) && isset($other['OPERATOR'][$column_name])){
                    $query->where([$column_name, $placeholder, $other['OPERATOR'][$column_name]], [$placeholder => $column_params['params'][$placeholder]]);
                }
                else {
                    $query->where([$column_name, $placeholder], [$placeholder => $column_params['params'][$placeholder]]);
                }
            }


            if (!$query->run()->getFirst())
            {//does not already exist

                $validation_methods = $this->getValidationMethods();

                if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
                {
                    $template = $validation_methods[__FUNCTION__]['errorMessage'];

                    return sprintf($template, $this->getObject()->getPropertyLabel($property), $property_value);
                }

                return false;
            }

            return true;
        }
        else
        {
            return $this->getObject()->getPropertyLabel($property) . ' is not set and can not be checked if exists';
        }
        

    }
    
    public function regex($property,$other)
    {
        $property_value = $this->getObject()->getPropertyValue($property);

        if(!isset($other['pattern']) OR !is_string($other['pattern']))
        {
            throw new Exception('Regex pattern string not provided');
        }

        if(!preg_match($other['pattern'],$property_value))
        {
            $validation_methods = $this->getValidationMethods();

            if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
            {
                $template = $validation_methods[__FUNCTION__]['errorMessage'];

                return sprintf($template, 
                        $this->getObject()->getPropertyLabel($property));
            }

            return false;
        }
        
        return true;
    }

    /**
     * Confirms a property matches with it's confirmation
     * Confirm value must have _confirm suffix
     * @param $property
     * @param $other
     * @return bool|string
     * @throws Exception
     */
    public function confirm($property,$other)
    {
        $property_value = $this->getObject()->getPropertyValue($property);

        $property_value_confirm = $this->getObject()->getPropertyValue($property.'_confirm');

        if(empty($property_value_confirm))
        {
            throw new Exception('Confirmation property value is empty');
        }

        if($property_value_confirm!=$property_value)
        {

            if(!empty($other['errorMessage'])){
                return lang(strval($other['errorMessage']));
            }else {
                $validation_methods = $this->getValidationMethods();

                if (!empty($validation_methods[__FUNCTION__]['errorMessage'])) {
                    $template = $validation_methods[__FUNCTION__]['errorMessage'];

                    return sprintf($template,
                        $this->getObject()->getPropertyLabel($property));
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Is property value part of th allowed values
     * @param $property
     * @param $other
     * @return bool|string
     * @throws Exception
     */
    public function allowedValues($property, $other)
    {
        $property_value = $this->getObject()->getPropertyValue($property);

        //if null is allowed return true
        if(empty($property_value) && isset($other['allowNull']) && boolval($other['allowNull']))
        {
            return true;
        }

        if(!isset($other['allowedValues']) OR !is_array($other['allowedValues']))
        {
            throw new Exception('allowedValues data array not provided');
        }

        if(in_array($property_value, $other['allowedValues']))
        {
            return true;
        }
        else
        {
            $validation_methods = $this->getValidationMethods();

            if (!empty($validation_methods[__FUNCTION__]['errorMessage']))
            {
                $template = $validation_methods[__FUNCTION__]['errorMessage'];

                return sprintf($template,$property_value,
                    $this->getObject()->getPropertyLabel($property));
            }

            return false;
        }
    }




    public function getValidationMethods()
    {
        return array(
            'file'=>array('errorMessage'=> '%s is could not be uploaded'),
            'confirm'=>array('errorMessage'=> '%s does not match'),
            'alphanum' => array('errorMessage' => '%s is not an alphanumeric string'),
            'isint' => array('errorMessage' => '%s is not an integer'),
            'numeric' => array('errorMessage' => '%s is not a numeric'),
            'unique' => array('errorMessage' => '%s already exists.'),
            'exists' => array('errorMessage' => '%s does not exists with value %s.'),
            'email' => array('errorMessage' => '%s is not a valid email address.'),
            'required' => array('errorMessage' => '%s is required.'),
            'minLength' => array('errorMessage' => '%s must have a minimum length of %s characters.'),
            'maxLength' => array('errorMessage' => '%s must have a maximum length of %s characters.'),
            'maxValue' => array('errorMessage' => '%s must have a maximum value of %s.'),
            'minValue' => array('errorMessage' => '%s must have a minimum value of %s.'),
            'regex' => array('errorMessage' => '%s must be a monetary value.'),
            'allowedValues' => array('errorMessage' => '%s is not an allowed value for %s.')
        );
    }

    /**
     * Array containing all the error messages after validation if any
     * @return array
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }



}
