<?php
namespace Leo;

use BadFunctionCallException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Exception;
use Leo\Interfaces\I_Behaviour;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Object
 *
 * @author Barnabas
 */
class ObjectBase implements \ArrayAccess
{
    
    protected $_behaviour = [];

    public function __construct($config = null)
    {
        if(is_array($config) && count($config))
        {
            foreach($config as $property => $value)
            {
                $this->{'set'.$property}($value);
            }
        }
        
        $this->_start_();
    }

    /**
     * Called whenever a method is initialised
     * Override this method for any initialisation processes instead of using
     * the constructor
     */
    protected function _start_()
    {
        
    }
    
    protected function setBehaviours(array $behaviour)
    {
        $this->_behaviour = $behaviour;
    }

    public function __get($property_name)
    {
        $value = null;
        
        $method_name = 'get'. clean($property_name);
        
        /**
         * If a get method exist execute
         */
        if(method_exists($this, $method_name))
        {
            $value = call_user_func(array($this, $method_name));
        }
        elseif($this->hasBehaviour())
        {
            /**
            * check behaviour classes
            */
            $bh = $this->getBehaviourHandler();
            $value = $bh->checkBehaviourProperties($property_name);
            $this->updateBehaviour($bh->getBehaviour());
        }
        
        return $value;
    }

    /**
     * Check if a setter method exist and return any value from method else null
     * @param string $property_name
     * @param mixed $value
     * @return mixed
     */
    public function __set($property_name, $value)
    {
        /**
         * Does property have a set method, call that
         */
        $method_name = 'set'. clean($property_name);
        
        if(method_exists($this, $method_name ))
        {
            return call_user_func_array(array($this, $method_name), ct($value));
        }      
        elseif($this->hasBehaviour())
        {
         /**
         * set in behaviour class
         */
            $bh = $this->getBehaviourHandler();
            
            $response = $bh->setBehaviourProperties($property_name, $value);
            
            $this->updateBehaviour($bh->getBehaviour());
            
            return $response;
        }
    }

    /**
     * Magic method called
     * @param type $methodname
     * @param type $arguments
     * @return type
     * @throws Exception
     */
    public function __call($methodname, $arguments)
    {
        
        if (0 === strncmp('get', $methodname, 3))
        {
            $property_name = lcfirst(substr($methodname, 3));//converts eg getFirstname to firstname
            
           $clean_methodname = clean($methodname);
                       
            if(method_exists($this, $clean_methodname))
            {
                return $this->{$clean_methodname}();
            }
            elseif (property_exists($this, $property_name))
            {
                return $this->{$property_name};
            }
                       
        }
        
        
        if(0 === strncmp('set',$methodname, 3))
        {
            $property_name = lcfirst(substr($methodname, 3));//converts eg setFirstname to firstname

            $clean_methodname = clean($methodname);

            if(method_exists($this, $clean_methodname))
            {
               return call_user_func_array(array($this,$clean_methodname), $arguments); 
            }
            elseif(property_exists($this, $property_name))
            {
                $this->{$property_name} = current($arguments);
                return;
            }
        }
        
        if($this->hasBehaviour())
        {
            $bh = $this->getBehaviourHandler();
            
            $response = $bh->checkBehaviourMethods($methodname, $arguments);
            
            $this->updateBehaviour($bh->getBehaviour());
            
            return $response;
        }
            
        throw new BadFunctionCallException('Method '.$methodname . ' not found in '. $this);
    }

    /**
     * Returns an object from a configurable array
     * <pre>
     * [
     *      '_class_' => 'classNamespace',
     *      'property_name' => $property_value,
     *      'property_name' => $property_value
     * ]
     * </pre>
     * <p>Must have key _class_ which is the class namespace you want instantiated.
     * The rest are used to set instantiated object properties.</p>
     * @param array $config
     * @return object instantiated object
     * @throws Exception if an error occurs
     */
    public static function loadClass(array $config)
    {

        
        if (isset($config['_class_']))
        {
            try
            {
                $object = Leo::getComponent('di')->getClass($config);
                return $object;
            }
            catch (Exception $e)
            {
                throw $e;
            }
        }
        else
        {
            throw new Exception('_class_ key is missing in configuration array passed.');
        }
            
    }

    /**
     * Return behaviour array or a single instantiated behaviour class
     * <pre>
     * $user = new User();
     * $this->addBehaviour($user); //add behaviour
     * $this->getBehaviour(get_class($user)); //get behaviour
     * </pre>
     * @param string|null $behaviour_name
     * @return array
     * @throws Exception
     */
    public function getBehaviour($behaviour_name = '')
    {
        if($behaviour_name)
        {
            $bh = $this->getBehaviourHandler();
            return $bh->getBehaviour($behaviour_name);
        }
        
        return $this->_behaviour;
    }


    public function __toString()
    {
        return get_called_class();
    }

    /**
     * Does this class have any behaviour added to it. 
     * @return boolean
     */
    public function hasBehaviour()
    {
        return isset($this->_behaviour) && count($this->_behaviour);
    }

    /**
     * <p>
     * A Behaviour extends the functionality of the class it is attached to.
     * You can then call the methods and properties as though it where that of the class.</p>
     *
     * <p>A behaviour can be added in several ways:</p>
     * <pre>
     * <ul>
     * <li>$this->addBehaviour($object)</li>
     * <li>$this->addBehaviour($object, ['_class_'=>'classNamespace','property_name'=>'property_value'])</li>
     * <li>$this->addBehaviour($object, ['_class_'=> $object,'property_name'=>'property_value'])</li>
     * </ul>
     * </pre>
     *
     * @param string|object $behaviour_name
     * @param array $configArray behaviour object class or object's namespace string
     * @throws Exception
     */
    public function addBehaviour($behaviour_name, array $configArray = null)
    {
        $bh = $this->getBehaviourHandler();         
        if($bh->addBehaviour($behaviour_name, $configArray))
        {
            $this->updateBehaviour($bh->getBehaviour());
        }
         
    }
    
    /**
     * Update the behaviour of the class
     * @param array $behaviour
     */
    protected function updateBehaviour(array $behaviour)
    {
        $this->_behaviour = $behaviour;
    }


    /**
     * Returns the helper class that handles behaviour functionality of
     * objects.
     * @return \Leo\Interfaces\I_Behaviour
     * @throws Exception
     */
    protected function getBehaviourHandler()
    {
        $bh = Leo::reloadComponent(
                'behaviour',
                array(
                    '_behaviour' => $this->getBehaviour(),
                    'di' => Leo::gc('di')
                    ));
        
        if(!$bh instanceof I_Behaviour)
        {
             throw new Exception('An instance of I_Behaviour needed to handle object properties.');
        }
        
        return $bh;
    }

    public function offsetExists($offset)
    {
        return property_exists($this,$offset);
    }

    public function offsetUnset($offset)
    {
        throw new Exception('unset via array access not supported');
    }


    public function offsetGet($offset) {
        if ($this->offsetExists($offset)) {
            return $this->{"get$offset"}();
        }
    }

    public function offsetSet($offset, $value) {
        if ($this->offsetExists($offset)) {
            $this->{"set$offset"}($value);
        }
        else
        {
            throw new InvalidArgumentException($offset. " not a property of ".static::class);
        }
    }

}
