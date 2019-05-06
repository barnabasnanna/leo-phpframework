<?php

namespace Leo\Helpers\Formatter;

use Exception;
use Leo\ObjectBase;

/**
 * Description of I_Formatter
 *
 * @author barnabasnanna
 */
Class Base extends ObjectBase
{

    public $format;
    public $class_map = [];
    public $name;

    public function leo_class_map()
    {
        return array(
            'boolean' => 'Leo\Helpers\Formatter\Boolean',
            'date' => 'Leo\Helpers\Formatter\DateTime',
            'dateTime' => 'Leo\Helpers\Formatter\DateTime',
            'image' => 'Leo\Helpers\Formatter\Image',
            'currency'=>'Leo\Helpers\Formatter\Currency'
        );
    }

    public function getFormatters()
    {
        return array_merge($this->leo_class_map(), $this->class_map);
    }

    private function fetchClass($name_or_classpath)
    {
        $map = $this->getFormatters();

        if (isset($map[$name_or_classpath]))
        {
            return $map[$name_or_classpath];
        }

        return $name_or_classpath;
    }

    public function run($config, $value)
    {
        $class = $this->getClass($config);

        return $class->format($value);
    }

    private function getClass($_config)
    {

        if (is_string($_config))
        {
            //check if in map
            $_config = $this->fetchClass($_config);
        }
        elseif (is_array($_config) && !empty($_config))
        {
            //check if in map
            $config = $this->fetchClass(array_shift($_config));

            if (is_string($config))
            {//if just classpath
                $_config['_class_'] = $config;
            }
            elseif (is_array($config))
            {//if array config
                $_config = array_merge_recursive($config, $_config);
            }
            
        }

        $class = leo()->getDi()->getClass($_config);

        if (!$class instanceof I_Formatter)
        {
            throw new \Exception(get_class($class) . ' is not a instance of \Leo\Helpers\Formatter\I_Formatter');
        }

        return $class;
    }

}
