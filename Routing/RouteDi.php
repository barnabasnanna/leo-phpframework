<?php
namespace Leo\Routing;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Dependency injector for routing.
 *
 * Passes the right argument to the action method
 *
 * @author Barnabas
 */
class RouteDi
{

    //function ($a, $b)
    //array('b'=>12, 'a'=>13)
    public static function getMethodParams(\ReflectionMethod $RM, array $user_parameters=[], array $route_expected_parameters=[])
    {
        $paramNames = [];
        //make sure that minimum required parameters are provided
        $num_of_required_params = $RM->getNumberOfRequiredParameters();

        if ($num_of_required_params > count($user_parameters))
        {
            throw new \Exception('Not enough required parameters provided');

        }elseif(count($route_expected_parameters) > count($user_parameters)){
            throw new \Exception('Not enough expected parameters are provided');
        }

        $methodParams = $RM->getParameters();

        foreach ($methodParams as $RP)
        {
            $type = (string)$RP->getType();

            if($RP->getType() && !$RP->getType()->isBuiltin()){//if class instantiate
                $paramNames[$RP->getName()] = static::getClass($RP);
            }else{
                $paramNames[$RP->getName()] = self::getValue($RP, $user_parameters);
            }
        }

        return $paramNames;
    }

    private static function getClass($RP){
        $className = (string) $RP->getType();
        if(!class_exists($className)){
            throw new Exception('Class not found');
        }
        return new $className;
    }

    private static function getValue(\ReflectionParameter $RP, array $user_param)
    {
        $value = null;

        $paramName = $RP->getName();

        //if the param doesnt exist in provided user params
        if (!array_key_exists($paramName, $user_param))
        {
            //assign default value if one exist
            if ($RP->isDefaultValueAvailable())
            {
                $value = $RP->getDefaultValue();
            }
            //if it is required, and default value not provided throw Exception
            elseif (!$RP->isOptional())
            {
                throw new \Exception(
                    sprintf('$%s is a required parameter for method %s::%s and no default value provided.',
                        $paramName,
                        $RP->getDeclaringClass()->getName(),
                        $RP->getDeclaringFunction()->getName()));
            }
        }
        else
        {
            //if provided, is it the right type
            $value = $user_param[$paramName];
            //type check
            if (!self::compareTypes($RP, $value))
            {
                throw new \InvalidArgumentException(sprintf('$%s is not the same type as %s.', $paramName, $value));
            }
        }

        return $value;
    }

    /**
     * Can a variable be assigned to a method parameter.
     * This checks variable types to see what can be accepted by method parameter.
     * @param mixed $method_parameter the variable to receive the value
     * @param mixed $passed_value the value to be assigned
     * @return boolean
     */
    protected static function compareTypes($method_parameter, $passed_value): bool
    {
        $method_parameter_type = self::getVariableType($method_parameter);
        $passed_value_type = self::getVariableType($passed_value);

        //if $method_parameter_type can be any then return true
        return $method_parameter_type==='any' OR $method_parameter_type === $passed_value_type;
    }

    protected static function getVariableType($variable): string
    {
        $type = null;

        if ($variable instanceof \ReflectionParameter)
        {
            $isAClass = $variable->getType() && !$variable->getType()->isBuiltin();
            if ($isAClass)
            {
                return 'object';
            }
            elseif ('array' === (string) $variable->getType())
            {
                return 'array';
            }
            else
            {
                return 'any';
            }
        }
        else
        {
            $type = \gettype($type);
        }

        return $type;
    }
}
