<?php
namespace Leo\Form\File\Validator;

/**
 * Used to ensure that an uploaded file meets certain requirements 
 * before it can be uploaded
 * @author Barnabas Nanna
 */
class fileUploadValidator
{
    protected $model;
    protected $files = null;
    protected $minFileSize = '2K';
    protected $maxFileSize = '60M';
    private $_errors = [];
    private $name,$size,$type,$tmp_name,$key,$error;
    protected $allowed_extensions = ['jpeg','jpg','png'];

    protected function key_exists()
    {
        $this->model = 'PropertyForm';
        $name= $_FILES[$this->model]['name'][$this->key];
        $type= $_FILES[$this->model]['type'][$this->key];
        $error= $_FILES[$this->model]['error'][$this->key];
        $size = $_FILES[$this->model]['size'][$this->key];
        $tmp_name = $_FILES[$this->model]['tmp_name'][$this->key];
        
        if(!isset($_FILES[$this->key]))
        {
           // throw new \Exception($this->key . ' is not found in $_FILES array');
        }
        
        $this->files = compact('name','type','error','size','tmp_name');
        
        return true;
    }
        
    protected function config($config = [])
    {
        foreach ($config as $key =>$value)
        {
            $this->{$key} = $value;
        }
        
    }
    
    public function checkFiles($config)
    {  
        
        $this->config($config);
                
        if($this->key_exists())
        {
            $amount = count($this->files['name']);
            for ($counter=0; $counter < $amount; $counter++)
            {
                $this->name = $this->files['name'][$counter];
                 $this->size = $this->files['size'][$counter];
                 $this->type = $this->files['type'][$counter];
                 $this->error = $this->files['error'][$counter];
                 $this->tmp_name = $this->files['tmp_name'][$counter];
                if($this->errorFile())
                    continue;
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
        if($this->error!==UPLOAD_ERR_OK)
        {
            $this->_errors[$this->name][] = 'File has an error';
            return true;
        }
        
        return false;
    }
    
    public function passedValidation()
    {
        $errors = $this->getFileErrors();
        
        return !count($errors);
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
            $this->_errors[$this->name][] = 'File '.$this->name.' too small';
        }
    }
    
    protected function isTooLarge()
    {
        if($this->size > $this->getSize($this->maxFileSize))
        {
            $this->_errors[$this->name][] = 'File '.$this->name.' too large';
        }
    }
    
    
    protected function validateExt()
    {
        if(extension_loaded ( 'fileinfo' ))
        {
            $this->extUsingPathInfo($this->name);
        }
        else
        {
            $name_elements = explode('.', $this->name);
            
            $extension = end($name_elements);
            
            if(!in_array($extension, $this->allowed_extensions))
            {
                $this->_errors[$this->name][] = 'File extension '.$extension.' not allowed';
            }
        }
        
    }
    
    /**
     * Use fileinfo extension to verify file extension
     * @param type $file_name
     * @throws \Exception
     */
    protected function extUsingPathInfo($file_name)
    {
        $path_parts = \pathinfo($file_name);
        if(!is_array($this->allowed_extensions))
        {
            throw new \Exception('Allowed extension must be an array');
        }
        
        if(!in_array($path_parts['extension'], $this->allowed_extensions))
        {
           $this->_errors[$this->name][] = 'File extension '.$path_parts['extension'].' not allowed';
        }
    }
    
    protected function checkServerSettings()
    {
        $upload_limit = ini_get('upload_max_filesize');
        $post_limit = ini_get('post_max_size');
        
        if($this->size > $this->getSize($upload_limit) OR $this->size > $this->getSize($post_limit))
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
    
}
