<?php
namespace Leo\Form\File\Validator;

/**
 * Used to ensure that an uploaded file meets certain requirements 
 * before it can be uploaded
 * @author Barnabas Nanna
 */
class singleFileUploadValidator
{

    protected $inputName;
    protected $files = null;
    protected $minFileSize = '2K';
    protected $maxFileSize = '60M';
    private $_errors = [];
    private $name, $size, $type, $tmp_name, $error;
    protected $allowed_extensions = ['jpeg', 'jpg', 'png'];
    
    protected function config($config = [])
    {
        foreach ($config as $key => $value)
        {
            if(property_exists($this, $key))
            {
                $this->{$key} = $value;
            }
        }
    }

    protected function key_exists()
    {
        if(isset($_FILES[$this->inputName]))
        {
            $name = $_FILES[$this->inputName]['name'];
            $type = $_FILES[$this->inputName]['type'];
            $error = $_FILES[$this->inputName]['error'];
            $size = $_FILES[$this->inputName]['size'];
            $tmp_name = $_FILES[$this->inputName]['tmp_name'];

            $this->files = compact('name', 'type', 'error', 'size', 'tmp_name');

            return true;
        }
        return false;
    }

    public function checkFiles($config = [])
    {

        $this->config($config);

        if ($this->key_exists())
        {

            $this->name = $this->files['name'];
            $this->size = $this->files['size'];
            $this->type = $this->files['type'];
            $this->error = $this->files['error'];
            $this->tmp_name = $this->files['tmp_name'];
            if (!$this->errorFile())
            {
                $this->isTooSmall();
                $this->isTooLarge();
                $this->validateExt();
                $this->checkServerSettings();
            }
        }


        return $this->passedValidation();
    }

    protected function errorFile()
    {
        if ($this->error !== UPLOAD_ERR_OK)
        {
            $this->_errors[$this->name][] = 'File ('.$this->name.') has an error';
            return true;
        }

        return false;
    }

    public function passedValidation()
    {
        return !count($this->getFileErrors());
    }

    public function getFileErrors()
    {
        return $this->_errors;
    }

    protected function hasFileErrors()
    {
        return !!count($this->getFileErrors());
    }

    protected function isTooSmall()
    {
        if ($this->size < $this->getSize($this->minFileSize))
        {
            $this->_errors[$this->name][] = 'File ' . $this->name . ' too small';
        }
    }

    protected function isTooLarge()
    {
        if ($this->size > $this->getSize($this->maxFileSize))
        {
            $this->_errors[$this->name][] = 'File ' . $this->name . ' too large';
        }
    }

    protected function validateExt()
    {
        if (extension_loaded('fileinfo'))
        {
            $this->extUsingPathInfo($this->name);
        }
        else
        {

            $extension = end(explode('.', $this->name));

            if (!in_array($extension, $this->allowed_extensions))
            {
                $this->_errors[$this->name][] = 'File extension ' . $extension . ' not allowed';
            }
        }
    }

    /**
     * Use fileinfo extension to verify file extension
     * @param string $file_name
     * @throws \Exception
     */
    protected function extUsingPathInfo($file_name)
    {
        $path_parts = \pathinfo($file_name);
        if (!is_array($this->allowed_extensions))
        {
            throw new \Exception('Allowed extension must be an array');
        }

        if (!in_array($path_parts['extension'], $this->allowed_extensions))
        {
            $this->_errors[$this->name][] = 'File extension ' . $path_parts['extension'] . ' not allowed';
        }
    }

    protected function checkServerSettings()
    {
        $upload_limit = ini_get('upload_max_filesize');
        $post_limit = ini_get('post_max_size');

        if ($this->size > $this->getSize($upload_limit) OR $this->size > $this->getSize($post_limit))
        {
            $this->_errors[$this->name][] = 'Check your server settings';
        }
    }

    protected function getSize($size = '')
    {
        $int = substr($size, 0, -1);

        switch (substr($size, -1))
        {
            case 'K':
                $mul = 1024;
                break;
            case 'M':
                $mul = 1024 * 1024;
                break;
            case 'G':
                $mul = 1024 * 1024 * 1024;
                break;
        }

        return $int * $mul;
    }

    /**
     * @return mixed
     */
    public function getInputName()
    {
        return $this->inputName;
    }

    /**
     * @param mixed $inputName
     * @return singleFileUploadValidator
     */
    public function setInputName($inputName)
    {
        $this->inputName = $inputName;
        return $this;
    }

    /**
     * @return string
     */
    public function getMinFileSize()
    {
        return $this->minFileSize;
    }

    /**
     * @param string $minFileSize
     * @return singleFileUploadValidator
     */
    public function setMinFileSize($minFileSize)
    {
        $this->minFileSize = $minFileSize;
        return $this;
    }

    /**
     * @return string
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    /**
     * @param string $maxFileSize
     * @return singleFileUploadValidator
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;
        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->allowed_extensions;
    }

    /**
     * @param array $allowed_extensions
     * @return singleFileUploadValidator
     */
    public function setAllowedExtensions(array $allowed_extensions)
    {
        $this->allowed_extensions = $allowed_extensions;
        return $this;
    }



}
