<?php
namespace Leo\Components\Lists;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Horizontal
 *
 * @author barnabasnanna
 */
class Horizontal
{

    public $amount;
    public $how_many_columns;
    public $items;
    public $template = null;
    public $listClass = '';
            
    function __construct(array $items, $how_many_columns)
    {
        $this->how_many_columns = $how_many_columns;
        $this->items = $items;
        $this->amount = count($items);
    }

    public function __toString()
    {
        $chunks = array_chunk($this->items, $this->how_many_columns);
        $class = $this->getClass();
        $lists='';
        
        foreach($chunks as $chunk)
        {
            $lists.= '<div class="row '.$this->listClass.'">';
            
            foreach($chunk as $item)
            {
                $lists.= '<div class="'.$class.'">'.$this->renderTemplate($item).'</div>';
            }
            
            $lists.= '</div>';
        }
        
        return $lists;
    }
    
    /**
     * 
     * @param mixed $item
     * @return string
     */
    protected function renderTemplate($item)
    {
        if($this->template instanceof \Closure)
        {
            $closure = $this->template;
            return $closure($item);
        }
        
        return $item;
    }


    protected function getClass()
    {
        switch($this->how_many_columns)
        {
            case 1:
                $class = 'col-md-12';
            break;
            case 2:
                $class = 'col-xs-6 col-lg-6 col-md-6 col-sm-6';
            break;
            case 3:
                $class = 'col-xs-6 col-lg-4 col-md-4 col-sm-4';
            break;
            case 4:
                $class = 'col-xs-6 col-lg-3 col-md-3 col-sm-4';
            break;
            default:
                $class = 'col-xs-6 col-lg-6 col-md-6 col-sm-6';
        }
        
        return $class;
    }
    
    /**
     * Closure used to render each items
     * @param \Closure $template
     */
    public function setTemplate(\Closure $template)
    {
        $this->template = $template;
    }

}
