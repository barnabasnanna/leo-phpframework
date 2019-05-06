<?php

namespace Leo\Form\Input;

use app\Packages\DataSets\Models\Attribute;
use Leo\Form\File\singleFileUploadValidator;
use Leo\Form\Input\Input;
use ErrorException;

/**
 * Input file element
 * @author barnabasnanna
 */
class File extends Input
{

    protected $type = 'file';
    protected $multiple = false;
    protected $template = '<label %s for="%s">%s</label> 
            <input type="file" id="%s" name="%s" %s value="%s"/>%s<br/>';
    protected $multi_template = '<label %s for="%s">%s</label> 
            <input type="file" id="%s" name="%s[]" %s value="%s" multiple/>%s<br/>';
    protected static $files, $response;

    public function getMultiple()
    {
        return $this->multiple;
    }

    public function setMultiple($multiple)
    {
        $this->multiple = !!$multiple;
        return $this;
    }

    public function rules()
    {
        
    }

    public function validate()
    {
        
    }

    public function getTemplate()
    {
        if ($this->multiple)
        {
            return $this->multi_template;
        }

        return $this->template;
    }

    public function __toString()
    {
        try
        {
            return sprintf($this->getTemplate(),
                $this->getLabelOptions(),
                $this->getId(),
                $this->getLabel(),
                $this->getId(),
                $this->getName(),
                $this->getOptions(),
                $this->getValue(),
                $this->getHint());
        }
        catch (\Exception $ex)
        {
            return $ex->getMessage();
        }
    }


}
