<?php
namespace Leo\Helpers\Formatter;
use Leo\ObjectBase;

/**
 * Description of Boolean
 *
 * @author barnabasnanna
 * Date : 24/11/2016
 */
class Image extends ObjectBase implements I_Formatter
{
    protected $value;
    protected $basePath = WEB_ROOT;
    protected $options = [];

    /**
     * @param string $filePath
     * @return string
     */
    public function format($filePath)
    {
        if(FALSE!==stripos($filePath, 'http'))
        {
            return '<img src="'.$filePath.'" '.$this->getOptionString().'/>';
        }
        elseif($filePath && file_exists($this->basePath.DS.$filePath))
        {
            return '<img src="'.DS.$filePath.'" '.$this->getOptionString().'/>';
        }

        return '';
    }

    private function getOptionString()
    {
        $optionString = '';
        if(is_array($this->options))
        {
            foreach ($this->options as $key=>$value)
            {
                $optionString .= " $key = '$value' ";
            }
        }

        return $optionString;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return Image
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param string $basePath
     * @return Image
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return Image
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }



}
