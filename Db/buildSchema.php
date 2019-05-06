<?php

namespace Leo\Db;

/**
 * BuildSchema for building and manipulating table schemas
 *
 * @author barnabasnanna
 */

class buildSchema extends \Leo\Helpers\Constructor
{

    protected $s = '';
    protected $tableName = '';
    protected $int = array();
    protected $tinyInt = array();
    protected $bigInt = array();
    protected $varChar = array();
    protected $primaryKey = array();
    protected $datetime = array();
    protected $timestamp = array();
    protected $text = [];
    protected $autoId = array();
    protected $charset = 'utf8mb4';
    protected $unique_index = [];
    protected $dropTableBeforeCreation = false;

    public function autoId($name)
    {
        $this->autoId[] = "`$name` bigint(20) unsigned NOT NULL AUTO_INCREMENT";
        $this->primaryKey[] = "PRIMARY KEY (`$name`)";
        return $this;
    }

    public function create($table, $ifNotExists = true)
    {
        $this->tableName = strval($table);
        
        $this->s = "CREATE TABLE " . ( $ifNotExists ? " IF NOT EXISTS " : "")
            . "  `$table` (%s)";
        return $this;
    }


    public function int($name, $length = 11, $default = 0, $unsigned = true)
    {
        $length = $length <= 11 ? $length : 11;
        $this->int[] = " `$name` int($length) ".( $unsigned ? "unsigned" : "")." NOT NULL DEFAULT $default";
        return $this;
    }

    public function tinyInt($name, $length = 4, $default = 0, $unsigned = true)
    {
        $length = $length <= 4 ? $length : 4;
        $this->tinyInt[] = " `$name` int($length) ".( $unsigned ? "unsigned" : "")." NOT NULL DEFAULT $default";
        return $this;
    }

    public function unique($name, $indexName=''){
        $this->unique_index[] = " UNIQUE KEY $indexName ($name)";
        return $this;
    }

    /**
     * Create the Big Int field
     * @param string $name column name
     * @param int $length
     * @param int $default
     * @param bool $unsigned
     * @return $this
     */
    public function bigInt($name, $length = 20, $default = 0, $unsigned = true)
    {

        $length = $length <= 20 ? $length : 20;
        $this->bigInt[] = " `$name` bigint($length) ".( $unsigned ? "unsigned" : "")." NOT NULL DEFAULT $default";
        return $this;
    }

    /**
     * Create a Varchar column
     * @param $name
     * @param int $length
     * @param string $default
     * @return $this
     */
    public function varChar($name, $length = 255, $default ='')
    {
        $length = $length <= 255 ? $length : 255;

        $this->varChar[] = "`$name` varchar($length) NOT NULL DEFAULT ''";
        return $this;
    }

    public function dateTime($name, $notNull=true, $default = '0000-00-00 00:00:00')
    {
        if($notNull) {
            $this->datetime[] = "`$name` DATETIME NOT NULL default '$default'";
        }else {
            $this->datetime[] = "`$name` DATETIME NULL";
        }
        return $this;
    }

    public function date($name, $notNull=true, $default = '0000-00-00')
    {
        if($notNull) {
            $this->datetime[] = "`$name` DATE NOT NULL default '$default'";
        }else {
            $this->datetime[] = "`$name` DATE NULL";
        }
        return $this;
    }

    public function timestamp($name='modified_date')
    {
        $this->timestamp[] = "`$name` timestamp NOT NULL DEFAULT NOW() ON UPDATE NOW()";
        return $this;
    }

    public function text($name)
    {
        $this->text[] = "`$name` text";
        return $this;
    }

    protected function createQuery()
    {
        if($this->dropTableBeforeCreation){//drop the table first
            $this->s = "DROP TABLE IF EXISTS {$this->tableName};". $this->s;
        }
        
        $sql = implode(',', array_merge($this->autoId, $this->int, $this->tinyInt, $this->bigInt, $this->varChar, $this->datetime, $this->timestamp, $this->text, array_unique($this->unique_index)));
        $this->setSql(sprintf($this->s, $this->primaryKeyString() . $sql)." ENGINE=InnoDB DEFAULT CHARSET={$this->charset};");
    }

    private function primaryKeyString()
    {
        return  count($this->primaryKey) ? array_pop($this->primaryKey) . ',' : "";
    }

    public function charset($charset){
        $this->charset = $charset;
        return $this;
    }

    public function drop(){
        $this->dropTableBeforeCreation = true;
        return $this;
    }

}
