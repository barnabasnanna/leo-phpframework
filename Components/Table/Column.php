<?php
namespace Leo\Components\Table;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Column
 *
 * @author barnabasnanna
 */
class Column
{
    public $value;
    
    public function run($model)
    {
        return $this->$value;
    }
}
