<?php
namespace Leo\Components\Lists;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * Description of Vertical
 *
 * @author barnabasnanna
 */
class Vertical
{

    public $amount;
    public $columns;
    public $max_rows;
    public $row_count_store;
    public $items;
    
    function __construct(array $items, $columns)
    {
        $this->columns = $columns;
        $this->items = $items;
        $this->amount = count($items);
    }

    function calMaxRows()
    {
        return $this->max_rows = ($this->amount < $this->columns) ? 1 : floor($this->amount / $this->columns);
    }

    function getRemainder()
    {
        return $this->amount % $this->columns;
    }

    function hasRemainder()
    {
        return $this->getRemainder() > 0;
    }

    function __toString()
    {
        
    }

    function getRowCountStore()
    {
        $this->row_count_store = array_fill(0, $this->calColumns(), $this->calMaxRows());

        if ($this->hasRemainder() && $this->amount > $this->columns)
        {
            $this->row_count_store [] = $this->getRemainder();
        }

        return $this->row_count_store;
    }

    function getColumnCountStore()
    {
        $this->row_count_store = array_fill(0, $this->calMaxRows(), $this->columns);

        if ($this->hasRemainder() && $this->amount > $this->columns)
        {
            $this->row_count_store [] = $this->getRemainder();
        }

        return $this->row_count_store;
    }

    function calColumns()
    {
        return ($this->amount > $this->columns) ? $this->columns : 1;
    }

}
