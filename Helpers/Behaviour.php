<?php
namespace Leo\Helpers;

use \Leo\Interfaces\I_Behaviour;
use Leo\Leo;

/**
 * Behaviour handles behaviour properties of classes
 * @author Barnabas
 */
class Behaviour implements I_Behaviour
{
    const PRIORITY = '_priority_';
    const _CLASS    = '_class_';
    /**
     * Dependency injector used to load behaviour classes
     * 
     * @var object
     */
    public $di = null;
    /**
     * Should behaviour priority be sorted befor search
     * @var boolean default is true
     */
    public $sortByPriority = true;
    
    public $_behaviour = array();
    
    /**
     * Can magic methods set the values of attached behaviour classes
     * @var boolean 
     */
    public $setBehaviourPropertyValue = true;

    /**
     * Returns the maximum priority index value of a class behaviour array
     * @return integer
     */
    public function getPriorityIndex()
    {
        $priorities = $this->getPriority();

        if(count($priorities))
        {
            return max($priorities);
            
        }
        
        return 0;
        
    }
    
    /**
     * Return the priority columns of behaviour multi dimensional array
     * @return int
     */
    public function getPriority()
    {
        if(!$this->hasBehaviour())
        {
            return array();
        }
            
        return array_column($this->getBehaviour(), self::PRIORITY);
    }


    /**
     * Sort behaviour according to their priority
     * @param array $behaviour
     * @return array
     */
    public function sortPriority(array $behaviour = array())
    {

        $priority = array();

        foreach ($behaviour as $key => $row)
        {
            $priority[$key] = isset($row[self::PRIORITY]) ? $row[self::PRIORITY] : 0;
        }

        array_multisort($priority, SORT_DESC, $behaviour);

        return $behaviour;
    }

    /**
     * sort behaviours according to their priority
     * @param type $behaviour
     * @return type
     */
    private function sortPriority2($behaviour = array())
    {
        uasort($behaviour, function ($item1, $item2) {
            if ($item1[self::PRIORITY] == $item2[self::PRIORITY])
            {
                return 0;
            }
            return $item1[self::PRIORITY] > $item2[self::PRIORITY] ? -1 : 1;
        });

        return $behaviour;
    }
    
    /**
     * 
     * @return boolean
     */
    public function hasBehaviour()
    {
        return is_array($this->_behaviour) && count($this->_behaviour);
    }

    /**
     * Assign value to behaviour property. If the setter method returns a value, that will be returned
     * <pre>
     * public function setPassword($password)
     * {
     *      if(strlen($password) < 8)
     *        {
     *            return false;
     *        }else{
     *          $this->password = true;
     *        }
     * }
     * </pre>
     * @param string $property_name
     * @param string $value
     * @return mixed null or value returned from setter method
     */
    public function setBehaviourProperties($property_name, $value)
    {
        $response = null;
        
        if ($this->hasBehaviour() && $this->setBehaviourPropertyValue)
        {
            if($this->getSortByPriority())
            {
                $this->_behaviour = $this->sortPriority($this->_behaviour);
            }

            foreach ($this->_behaviour as $behaviour_name => $configArray)
            {
                if (isset($configArray[self::_CLASS]))
                {
                    $object = $configArray[self::_CLASS];
                    
                    if(is_string($object))
                    {
                        /**
                         * remove and temp store _priority_ from config array so doesnt throw error
                         * as not a property of the instantiated object. After instantiation, restore
                         */
                        $priority = $configArray[self::PRIORITY];
                        unset($configArray[self::PRIORITY]);
                        $object = $this->getDi()->getClass($configArray);
                        $this->_behaviour[$behaviour_name][self::_CLASS] = $object;
                        $this->_behaviour[$behaviour_name][self::PRIORITY] = $priority;
                    }
                    
                    $methodname = 'set'.clean($property_name);
                    
                    if(method_exists($object, $methodname))
                    {//if the property has a setter method, use that instead
                       $response = call_user_func_array(array($object, $methodname), $this->ct($value));
                        break;
                    }elseif(property_exists($object, $property_name))
                    {//else attempt to set if public
                        $object->{$property_name} = $value;
                        break;
                    }
                }
            }
        }
        
        return $response;
                
    }
    
    
    /**
     * When an unknown property is requested, the __get method calls this method to check if
     * any behaviour classes has it. If found, it returns the property value.
     * @param string $property_name
     * @return property value if found and null if not
     */
    public function checkBehaviourProperties($property_name)
    {
        $value = null;

        if ($this->hasBehaviour())
        {
            if($this->getSortByPriority())
            {
                $this->_behaviour = $this->sortPriority($this->_behaviour);
            }
            foreach ($this->_behaviour as $behaviour_name => $configArray)
            {
                if (isset($configArray[self::_CLASS]))
                {
                    $object = $configArray[self::_CLASS];
                    
                    if(is_string($object))
                    {
                        /**
                         * remove and temp store _priority_ from config array so doesnt throw error
                         * as not a property of the instantiated object. After instantiation, restore
                         */
                        $priority = $configArray[self::PRIORITY];
                        unset($configArray[self::PRIORITY]);
                        $object = $this->getDi()->getClass($configArray);
                        $this->_behaviour[$behaviour_name][self::_CLASS] = $object;
                        $this->_behaviour[$behaviour_name][self::PRIORITY] = $priority;
                    }
                    
                    $methodname = 'get'.clean($property_name);
                    
                    if(method_exists($object, $methodname))
                    {//if there is a get method for the property use that
                        $value = call_user_func(array($object, $methodname));
                        break;
                    }elseif (property_exists($object, $property_name))
                    {//else attempt to get if public
                        $value = $object->{$property_name};
                        break;
                    }
                        
                }
            }
        }

        return $value;
    }

    /**
     * When an unknown method is called on an object, this method is used to check if it exists 
     * in it's behaviour array. If found, it is called and the result returned else null
     * @param string $methodname name of method being search for
     * @param array $args arguments passed to method
     * @return mixed Result of called method if one found else null
     */
    public function checkBehaviourMethods($methodname, $args)
    {
        $value = null;
        
        if ($this->hasBehaviour())
        {
            
            if($this->getSortByPriority())
            {
                $this->_behaviour = $this->sortPriority($this->_behaviour);
            }
            
            foreach ($this->_behaviour as $behaviour_name => $configArray)
            {
                
                if (isset($configArray[self::_CLASS]))
                {
                    $object = $configArray[self::_CLASS];
                    
                    if(is_string($object))
                    {
                        /**
                         * remove and temp store _priority_ from config array so doesnt throw error
                         * as not a property of the instantiated object. After instantiation, restore
                         */
                        $priority = $configArray[self::PRIORITY];
                        unset($configArray[self::PRIORITY]);
                        $object = $this->getDi()->getClass($configArray);
                        $this->_behaviour[$behaviour_name][self::_CLASS] = $object;
                        $this->_behaviour[$behaviour_name][self::PRIORITY] = $priority;
                    }
                    
                    
                    if(method_exists($object, $methodname))
                    {
                        
                        $value = call_user_func_array(
                                array($object, $methodname), 
                                $args
                        );
                        
                        break;
                    }
                }
            }
        }

        return $value;
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
* <li>$this->addBehaviour(name, [self::_CLASS=>'classNamespace', self::PRIORITY=>2', 'property_name'=>'property_value'])</li>
* <li>$this->addBehaviour(name, [self::_CLASS=> $object, self::PRIORITY=>2'])</li>
* </ul>
* </pre>
* 
* @param string|object $behaviour_name
* @param array $config configurable array
* @return boolean
*/
    public function addBehaviour($behaviour_name, array $config = null)
    {
        $priortyIndex = $this->getPriorityIndex() + 1;
        /**
         * Get and store the priority index to inject in the configurable array of the behaviour
         */
        $base = array(self::PRIORITY => $priortyIndex);
        
        $configArray = (array) $config;
        /**
         * If an object was given instead of a classNamespace,
         * Convert to proper format and pass again to method to store.
         * Also store any properties passed
         */
        if (is_object($behaviour_name))
        {
            $name = get_class($behaviour_name);
            $base[self::_CLASS] = $behaviour_name;
            $this->addBehaviour($name, array_replace_recursive($base, $configArray));
        }
        elseif (is_string($behaviour_name) &&
                isset($configArray[self::_CLASS]) && 
                (is_string($configArray[self::_CLASS]) 
                || is_object($configArray[self::_CLASS]) )
        )
        {
            /**
             * Store behaviour and it's properties
             */
            $name = sanitize($behaviour_name,true);
            $this->_behaviour[$name] = array_replace_recursive($base, $configArray);
        }
        else
        {
            return false;
        }

        return true;
    }
    
    private function getKey($behaviour_name)
    {
        return sanitize($behaviour_name, true);
    }

    
    /**
     * Return a behaviour if name is given else behaviour array
     * @param string $behaviour_name
     * @return array behaviour config array
     * @throws \Exception
     */
    public function getBehaviour($behaviour_name = '')
    {
        if(!$behaviour_name)
        {
            return $this->_behaviour;
            
        }else
        {
            $name = $this->getKey($behaviour_name);
            
            if(isset($this->_behaviour[$name]))
            {
                return $this->_behaviour[$name];
            }
            else
            {
                throw new \Exception($behaviour_name . lang(' not found in behaviour array.'));
            }
        }
                
    }
    
    public function removeBehaviour($behaviour_name)
    {
        $name = $this->getKey($behaviour_name);
        
        if($this->hasBehaviour() && array_key_exists($name, $this->_behaviour))
        {
           unset($this->_behaviour[$name]);
        }
        
    }
    
    public function getDi()
    {
        return $this->di;
    }
    
    public function setDi($di)
    {
        if(!is_object($di))
        {
            throw new \BadMethodCallException('$di must be an object');
        }
        
        $this->di = $di;
    }
    
    public function getSortByPriority()
    {
        return $this->sortByPriority;
    }
    
    public function setPrioritySort($value)
    {
        $this->sortByPriority = !! $value ;
    }
    
    /**
     * Object dont cast to arrays properly so this function handles that
     * @param mixed $var
     * @return array
     */
    private function ct($var)
    {
        if(is_object($var))
        {
           $var = array(0 => $var);
        }
        
        return (array) $var;
    }

}
