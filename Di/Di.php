<?php
namespace Leo\Di;

/**
 * Dependency injector and service locator.
 * Use to instantiate a class or method at runtime
 * @author Barnabas
 */
class Di
{
    /**
     * @var boolean should instantiated objects be cached
     * or instantiated every single time.
     * Default is true. Set to false to disable caching
     */
    private $cache = true;
    public static $instance;
    public $useNull = false;
    private $_container = array();

    public function __construct(){
    }

    /**
     * Instantiates the Dependency injector
     * @return \Leo\Di\Di
     */
    public static function ini()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static();
        }

        return self::$instance;
    }

    protected function checkNS($string)
    {
        //check string is a valid namespace
        //convert to a file path and check if it exists
        return true;
    }

    public function getContainer()
    {
        return $this->_container;
    }

    /**
     * Add an object, string or configurable array to Dependency Injector container.
     * Acceptable parameter formats.
     * <pre>
     * <ul>
     * <li>$this->add($object)</li>
     * <li>$this->add(classNamespace)</li>
     * <li>$this->add(string, $object)</li>
     * <li>$this->add(string, ['_class_'=>'classNamespace'])</li>
     * <li>$this->add(string, classNamespace)</li>
     * </ul>
     * </pre>
     * @param string|object $key
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    public function add($key, $value = null)
    {
        $c_array = array();

        if (!$value)
        {//if value not given eg when key is an object
            $value = $key;
        }

        if (is_object($value))
        {
            $c_array = $value;
        }
        elseif (is_string($value))
        {
            if ($this->checkNS($value))
            {
                $c_array['_class_'] = $value;
            }
        }
        elseif (is_array($value) && isset($value['_class_']))
        {//if it is a configuration array
            if ($this->checkNS($value['_class_']))
            {
                $c_array = $value;
            }
        }
        else
        {
            throw new \InvalidArgumentException('Invalid parameters passed');
        }

        $this->_container[$this->getKey($key)] = $c_array;

    }

    /**
     * Returns the string index used in container
     * @param mixed $key
     * @return string
     */
    private function getKey($key)
    {
        if(is_string($key))
        {
            return $key;
        }
        elseif(is_object($key))
        {
            return get_called_class($key);
        }
    }

    /**
     * get an object from di container.
     * First checks if the $key is an associated index in container and returns class instance from value.
     * If not, it checks if any of the values are object instances of $key
     */
    public function get($key, $throwException=false)
    {
        $container = $this->getContainer();

        $key = $this->getKey($key);

        if (isset($container[$key]))
        {
            $object = $container[$key];

            if (!is_object($object))
            {
                $object = $this->getClass($container[$key]);

                if ($this->getCache())
                {//cache the instantiated object
                    $this->add($key, $object);
                }
            }

            return $object;
        }
        elseif($object = $this->findClassInstance($key))
        {
            return $object;

        }
        elseif($throwException)
        {
            throw new \Exception($key . ' not added to container.');
        }
        else {
            return false;
        }
    }

    /**
     * Finds an instance of a class in the container values.
     * @param string $class_name
     * @return boolean
     */
    protected function findClassInstance($class_name)
    {
        foreach($this->getContainer() as $key => $value)
        {
            if (is_array($value) && isset($value['_class_']))
            {
                $value = $this->getClass($value);
            }

            if(is_object($value) && \get_called_class($value) == \get_called_class($class_name))
            {
                return $value;
            }
        }

        return false;
    }

    /**
     * Instantiates a class from a configuration array or string.
     * IF string, must be a class namespace
     * <pre>
     *  [
     *    '_class_'              => 'classNamespace',
     *    'property_name' => 'property_value',
     *    'property_name_2' => [
     *                              '_class_'=> 'classNamespace',
     *                              'property_name' => 'property_value'
     *                           ]
     * ]
     * </pre>
     * @param string|array $configArray
     * @return mixed instantiated object or false on failure
     * @throws \ReflectionException
     */
    public function getClass($configArray)
    {
        $config = $configArray;


        if(is_string($configArray))
        {
            $config = [];
            $config['_class_'] = $configArray;
        }
        elseif(isset($config['_class_']) && is_object($config['_class_']))
        {//if already an object
            return $config['_class_'];
        }

        $RC = new \ReflectionClass($config['_class_']);

        if($RC instanceof \ReflectionClass)
        {
            if ($RC->isInstantiable())
            {
                unset($config['_class_']);//remove the _class_ index

                $object = $this->instantiate($RC);

                $this->setProperties($config, $object);
            }
            else
            {
                throw new \ReflectionException($RC->getClass()->getName() . ' can not be instantiated.');
            }

        }
        else
        {
            throw new \ReflectionException($config['_class_'] . ' not found.');
        }

        return $object;
    }

     /**
     * <p>Sets the properties of an instantiated class.
     * Array key and values are used as property value pairs.</p>
     * If a property is an configurable array with a _class_ key, that is instantiated as well
     * <pre>
     * [
     *    'property_name' => 'property_value',
     *      'property_name_2' => [
     *                              '_class_'=> 'classNamespace',
     *                              'property_name' => 'property_value'
     *                           ]
     * ]
     * </pre>
     * @param array $config class properties
     * @param object $object object you want their properties modified
     * @throws \Exception if a property doesn't belong to class
      * @throws \BadMethodCallException if object not provided
     */
    public function setProperties(array $config, $object)
    {
        if(!is_object($object))
        {
            throw new \BadMethodCallException('Second argument must be an object');
        }

        foreach ($config as $property_name => $value)
        {
            if (property_exists($object, $property_name))
            {
                if (is_array($value) && isset($value['_class_']))
                {
                    $object->{$property_name} = $this->getClass($value);
                }
                else
                {
                    if(method_exists($object, 'set'. clean($property_name)))
                    {//use setter method if one exists
                        call_user_func_array(array($object, 'set' . clean($property_name)), $this->ct($value));
                    }
                     else
                    {
                         $object->{$property_name} = $value;
                    }

                }
            }
            else
            {
                throw new \Exception($property_name . lang(' is not a property of ') . get_class($object));
            }
        }
    }

    /**
     * Instantiating a new class.
     * @param \ReflectionClass $RC
     * @return type
     */
    private function instantiate(\ReflectionClass $RC)
    {
        $params = $this->getMethodParams($RC->getConstructor());

        return $RC->newInstanceArgs($params);
    }

    /**
     * Returns the relection method parameters
     * @param \ReflectionMethod $RM
     * @return array method parameters
     */
    public function getMethodParams(\ReflectionMethod $RM=null)
    {
        $params = [];
        if($RM instanceof \ReflectionMethod)
        {
            foreach ($RM->getParameters() as $RP)
            {
                $params[] = $this->fetchParamValue($RP);
            }
        }

        return $params;
    }

    private function fetchParamValue(\ReflectionParameter $RP)
    {
        $value = null;

        if ($RP->isOptional())
        {
            $value = $this->fetchParamDefaultValue($RP);
        }
        elseif (($class = $RP->getClass()))
        {//check if class is already stored in container
            $value = $this->get($class->getName()) ?:$class->newInstance();

        }
        elseif ($RP->isArray())
        {
            $value = array();
        }
        else
        {
            $value = null;
        }

        return $value;
    }

    private function fetchParamDefaultValue(\ReflectionParameter $RP)
    {
        $defaultValue = null;

        if ($RP->isDefaultValueAvailable())
        {
            $defaultValue = $RP->getDefaultValue();
        }

        return $defaultValue;
    }


    /**
     * Should instantiated objects be cached
     * @return bool
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Should instantiated objects by Di be cached
     * @param bool $cache_objects true means yes and false otherwise
     */
    public function setCache($cache_objects)
    {
        $this->cache = (bool) $cache_objects;
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
           $var = array($var);
        }

        return (array) $var;
    }

}
