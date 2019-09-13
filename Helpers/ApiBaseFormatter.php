<?php
namespace Leo\Helpers;

use Leo\ObjectBase;

/**
 * Removes
 */
abstract class ApiBaseFormatter extends ObjectBase
{
    public static $whiteList = [];

    public static function sieve(array $data, $prefix='')
    {
        foreach($data as $key => $value)
        {
            if(is_array($value))
            {

                foreach ($value as $key2=>$value2) {

                    if(is_array($value2))
                    {

                        foreach ($value2 as $key3=>$value3) {

                            if (!in_array($key . '.' . $key2 . '.'. $key3, static::$whiteList) OR empty($value3)) {
                                unset($data[$key][$key2][$key3]);
                            }
                        }
                    }elseif (!in_array($key . '.' . $key2, static::$whiteList) OR empty($value2)) {
                         unset($data[$key][$key2]);
                    }
                }
            }
            elseif(! in_array($key,static::$whiteList) OR empty($value))
            {
                unset($data[$key]);
            }
        }

        return $data;
    }

}