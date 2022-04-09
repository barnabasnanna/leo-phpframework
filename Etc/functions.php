<?php
/**
 * Translation messages
 * @param string $text string you want translated
 * @param string $lang language you want it translated to
 * @return string
 */
function lang($text = '', $lang = 'en')
{
     return leo()->t($text, $lang);
}

/**
 * Replace non alpha numeric characters with underscore
 * @param string $str string you want sanitize
 * @param boolean $lowerCase set to true if you want a lowercased version returned
 * @param string $replace
 * @return string sanitized string
 */
function sanitize($str = '', $lowerCase = false,$replace='_')
{
    $s = preg_replace('/\W/', $replace, $str);
    return $lowerCase ? strtolower($s) : $s;
}

/**
 * Converts $var to array
 * @param mixed $var
 * @return array
 */
function ct($var)
{
    if (is_object($var))
    {
        $var = array($var);
    }

    return (array) $var;
}

/**
 * removes non alpha numeric characters from string
 * @param string $str string you want cleaned
 * @param boolean $lowerCase set to true if you want a lowercased version returned
 * @param $replace string used to replace unwanted characters
 * @return string sanitized string
 */
function clean($str = '', $lowerCase = false, $replace='')
{
    $s = preg_replace('/[^a-zA-Z0-9]/', $replace, $str);
    return $lowerCase ? strtolower($s) : $s;
}

/**
 * Create a url or href link
 * @param string $text
 * @param array $href_params
 * @param array $attributes
 * @return string
 */
function Url(string $text='', array $href_params = [], array $attributes = []): string
{
    $options = '';
    foreach($attributes as $attribute => $value)
    {
        $options.= " $attribute = '$value' ";
    }

    $href = array_shift($href_params);
    $params = http_build_query($href_params);
    $link = $href.( count($href_params) ? '?'. $params : '') .'"' .$options;

    return $text ? '<a href="'.$link.'>'.$text.'</a>' : $link;
}

/**
 * Returns instance of Leo
 * @return Leo\Leo 
 */
function leo()
{
    return \Leo\Leo::init();
}

/**
 * Add quote to string
 */
function stringify($str)
{
    return "'$str'";
}

/**
 * Is value empty
 * @param $value
 * @return bool
 */
function isEmpty($value)
{
    return !is_bool($value) && !is_numeric($value) && empty($value);
}
