<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Leo\Components\Table\Column;

/**
 * RowIndex displays the row index value of the Table Component
 *
 * @author bnanna
 * Date 1/7/2016
 */
class RowIndex {
    
    protected $table_index;

    public function setTableIndex($table_index)
    {
        $this->table_index = $table_index;
    }
    
    /**
     * 
     * @param \Leo\Db\ActiveRecord $model
     * @return string
     */
    public function run($model)
    {
        return $this->table_index['row'];
    }
}
