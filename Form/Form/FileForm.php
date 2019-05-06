<?php
namespace Leo\Form\Form;

use Leo\Form\Input\File;
use Exception;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * FileForm for forms that submit images
 * @author barnabasnanna
 */
class FileForm extends AbstractForm
{
    public function __toString()
    {
        try
        {
            $e = '<div>'.$this->renderFormNotifcation().'</div>'
                    . '<form '.$this->getOptions().' enctype="multipart/form-data">'
                    .$this->renderElements().'</form>';
        }
        catch (Exception $ex)
        {
           $e = $ex->getMessage(); 
        }
        
        return $e;
    }
    
    public function file($name, array $options = [])
    {
        $file = new File($name,$options);
        $this->addElement($file);
        return $file;
    }
    
    public function image($name, array $options = [])
    {
        $image = new File($name,$options);
        $this->addElement($image);
        return $image;
    }
    
}
