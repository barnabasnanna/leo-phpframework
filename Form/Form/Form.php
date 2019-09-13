<?php
namespace Leo\Form\Form;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VerticalForm
 *
 * @author barnabasnanna
 */
class Form extends AbstractForm
{
            
    public function __toString()
    {
        try
        {
            $e = '<form '.$this->getOptions().' >'
                    . ''.$this->renderElements().
                    '</form>';
        }
        catch (\Exception $ex)
        {
           $e = $ex->getMessage(); 
        }
        
        return $e;
    }
    
    
    
}
