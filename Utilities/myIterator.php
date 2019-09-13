<?php
namespace Leo\Utilities;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of myIterator
 *
 * @author barnabasnanna
 */
class myIterator implements \Iterator , \Countable {
    private $position = 0;
    private $items = array();

    public function __construct() {
        $this->position = 0;
    }

    function rewind() {
        
        $this->position = 0;
    }

    function current() {
        
        return $this->items[$this->position];
    }

    function key() {
        
        return $this->position;
    }

    function next() {
        
        ++$this->position;
    }

    function valid() {
        
        return isset($this->items[$this->position]);
    }
    
    public function addItem($item)
    {
        if($item instanceof myIterator)
        {
            $this->items = array_merge($this->items, $item->getItems());
        }
        elseif(is_array($item))
        {
            $this->items = array_merge($this->items,$item);
        }
        else
        {
            $this->items[] = $item;
        }
        
    }
    
    public function getItems()
    {
        return $this->items;
    }
    
    public function count()
    {
        return count($this->getItems());
    }
}

