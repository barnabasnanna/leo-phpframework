<?php

namespace Leo\Code_Generator;
/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 10/06/2017
 * Time: 17:08
 */
class Model extends \Leo\Db\ActiveRecord
{
    public $table;

    public $namespace;

    public $columns;

    public function __construct($tableName)
    {
        $this->table = $tableName;
    }

    public function createClassFile()
    {
        if($this->doesTableExist($this->table))
        {
            $template = <<<CLASS
            <?php
            namespace app\Models\%sQuery;


use Leo\Db\ActiveRecord;

class %s extends ;
{
    %s\n
    
    %s\n
}


CLASS;

            if(!file_exists($this->getClassFilePath()))
            {
                $fhandle = fopen($this->getClassFilePath(), 'w');
                $content = sprintf($template, $this->getClassName(), $this->getClassName(), $this->getClassPropertiesString(), $this->getRulesString());

                fwrite($fhandle, $content);
                fclose($fhandle);
            }
            else{
                echo sprintf('%s filess already exists');
                die;
            }

        }
    }

    private function getRulesString()
    {
        return "\tpublic function rules(){}\n";
    }

    private function getClassFilePath()
    {
        $namespace = BASE_PATH.DS.'app\Models\DB';

        $filename = $this->getClassName();

        $path = str_replace('\\',DS,$namespace).DIRECTORY_SEPARATOR.$filename;

        return $path;
    }



    private function getClassPropertiesString()
    {
        $properties = [];

        foreach ($this->fetchTableColumns() as $property)
        {
            $properties[] = "\tprotected $$property;";
        }

        return "\n\n".join("\n", $properties)."\n\n";
    }


    protected function doesTableExist($tableName)
    {
        return true;
    }

    /**
     * Get the table columns to be used as class properties
     * @return array
     */
    private function fetchTableColumns()
    {
        return leo()->getTableInfo($this->table)->getColumns();
    }

    protected function getClassName()
    {
        return ucfirst(strtolower($this->table)).'.php';
    }



}