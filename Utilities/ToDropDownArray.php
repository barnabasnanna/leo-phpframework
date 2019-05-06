<?php
/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 03/12/2016
 * Time: 17:55
 */

namespace Leo\Utilities;


class ToDropDownArray
{

    /**
     * Converts a collection of active records to a drop down array
     * using specified columns of the model
     * @param array $activeRecordCollection
     * @param string $key_column
     * @param string $value_column
     * @return array
     */
    public static function getOptions(array $activeRecordCollection, $key_column, $value_column)
    {
        $options = [];

        foreach ($activeRecordCollection as &$record)
        {
            $options[$record->{$key_column}] = $record->{$value_column};
        }
        unset($record);

        return $options;
    }

}