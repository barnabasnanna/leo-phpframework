<?php
/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 05/12/2016
 * Time: 12:01
 */

namespace Leo\Utilities;


class CSVReader
{

    public static function csv_to_array($filename='', $delimiter=',')
    {
        ini_set('auto_detect_line_endings',TRUE);

        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
}