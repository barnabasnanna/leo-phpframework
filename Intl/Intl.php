<?php
namespace Leo\Intl;

use http\Exception\InvalidArgumentException;

/**
 * Offers support for translations
 * @package Leo\Intl
 */
class Intl extends \Leo\ObjectBase {
    public $lang = 'en';

    protected $format = 'array';

    /**
     * @var string The extension of the lang file
     */
    public $fileExtension = 'php';

    /**
     * @var string The path to the folder where the language files are located
     */
    public $basePath = 'languages';

    /**
     * @var array stores the file
     */
    private static $dictionary = [];

    /**
     * Supported return formats from the language file.
     * Default are array or ini file
     * @code
     *   return array('key'=> 'value')
     * @endcode
     * @var array
     */
    protected $supportedFormats = ['array','ini'];

    /**
     * @return string path to folder where the language files are
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Sets the location where the translations files are located
     * @param $languagesFolder
     * @return Intl
     */
    public function setBasePath($languagesFolder)
    {
        $this->basePath = $languagesFolder;
        return $this;
    }

    /**
     * Return the translated value of key if found else an empty string is returned
     * @param $key
     * @return string
     */
    public function translate($key)
    {
        if(!static::$dictionary){
            $contents = require $this->getBasePath().DS.$this->lang.'.'.$this->fileExtension;
            if(!is_array($contents) && $this->format === 'ini'){
                static::$dictionary = parse_ini_file($contents,TRUE);//return a multi dimensional array
            }elseif(is_array($contents)){
                static::$dictionary = $contents;
            }
            unset($contents);
        }

        if(is_array(static::$dictionary) && isset(static::$dictionary[$key]))
        {
            return static::$dictionary[$key];
        }

        return ucfirst(str_replace('_',' ',$key));//if not translation found, convert key to sentence
    }

    /**
     * Set the lang eg en
     * @param $lang
     * @return $this
     */
    public function setLang($lang)
    {
        $this->lang = (string) $lang;
        return $this;
    }

    /**
     * Return the lang short form
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set the format of data returned by language file.
     * Default format is array
     * @param string $format
     * @return $this
     */
    public function setFormat($format =  'array'){
        if(!in_array($format, $this->supportedFormats)){
            throw new \InvalidArgumentException($format . ' is not a support format. Supported formats are '. join('',$this->supportedFormats));
        }

        $this->format = $format;
        return $this;
    }

    /**
     * The allowed format for data format returned by language file
     * @param array $formats
     * @return $this
     */
    public function setFormattedFormats(array $formats){
        $this->supportedFormats = $formats;
        return $this;
    }
}
