<?php

namespace Leo\Code_Generator;

use Leo\Leo;
use Leo\MainModel;

/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 10/06/2017
 * Time: 17:08
 */
class ModelQuery extends MainModel
{
    /**
     * @var string Table name
     */
    public $table;

    /**
     * @var string Default namespace and location where files would be placed
     */
    public $namespace = 'app\Models\Db';

    public $columns;
    /**
     * @var bool Should existing files be overriden
     */
    public $override = false;


    public function createClassFile()
    {
        if ($this->doesTableExist($this->table)) {
            $template = <<<CLASS
<?php

namespace %s;


use Leo\Db\ActiveRecord;

class %s extends ActiveRecord
{
    %s\n
    %s\n
    %s\n
    %s\n
    %s\n
}


CLASS;

            $fileExists = file_exists($this->getClassFilePath());

            if (!$fileExists OR $this->override) {//file doest exist or override is true
                $fhandle = fopen($this->getClassFilePath(), 'w');

                if (is_resource($fhandle)) {
                    $content = sprintf($template, $this->getNameSpace(), $this->getClassName(),
                        $this->getClassPropertiesString(),
                        $this->getTableName(),
                        $this->getRulesString(),
                        $this->getClassGetters(),
                        $this->getClassSetters());

                    fwrite($fhandle, $content);

                    fclose($fhandle);

                    Leo::log($this->getClassName().'.php created.');
                }

            } else {

                Leo::log(sprintf('%s file already exists', $this->getClassFilePath()));

            }

        }else{
            Leo::log(sprintf('%s table does not exists in database', $this->table), LOG_TYPE_APP_ERROR);
        }
    }

    protected function getClassGetters(){
        $getters = [];

        foreach ($this->fetchTableColumns() as $property) {
            $func = "\tpublic function get".$this->camelCase($property)."()";
            $func .= "{\n";
            $func .= "\t\treturn \$this->$property;";
            $func .="\n\t}";
            $getters[]=$func;
        }

        return "\n" . join("\n\n", $getters);
    }

    protected function getClassSetters(){
        $setters = [];

        foreach ($this->fetchTableColumns() as $property) {
            $func = "\tpublic function set".$this->camelCase($property)."($$property)";
            $func .= "{\n";
            $func .= "\t\t \$this->$property = $$property;";
            $func .= "\n\t\treturn \$this;";
            $func .="\n\t}";
            $setters[]=$func;
        }

        return "\n" . join("\n\n", $setters);
    }
    
    protected function getNameSpace(){
        return str_replace('/', '\\', $this->namespace);
    }

    protected function getTableName()
    {
        return "public static function getTableName(){ return '{$this->table}';}";
    }

    protected function getRulesString()
    {
        return "public function rules(){ return parent::rules();}\n";
    }

    protected function getClassFilePath()
    {
        $namespace = BASE_PATH . DS . $this->getNameSpace();//'app\Models\DB';

        $filename = $this->getClassName() . '.php';

        $path = str_replace('\\', DS, $namespace) . DIRECTORY_SEPARATOR . $filename;

        return $path;
    }


    protected function getClassPropertiesString()
    {
        $properties = [];

        foreach ($this->fetchTableColumns() as $property) {
            $properties[] = "\tprotected $$property;";
        }

        return "\n\n" . join("\n", $properties) . "\n\n";
    }

    /**
     * Does the table exists
     * @param $tableName
     * @return bool
     * @throws \Exception
     */
    protected function doesTableExist($tableName)
    {
        $sql = "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME = :table
AND TABLE_SCHEMA in (SELECT DATABASE())";
        $count = \leo()->getDb()->params([':table'=>$tableName])->run($sql)->getFirst();
        return boolval($count);
    }

    /**
     * Get the table columns to be used as class properties
     * @return array
     */
    protected function fetchTableColumns()
    {
        return $this->columns?: ($this->columns = leo()->getTableInfo($this->table)->getColumns());
    }

    /**
     * Converts table name to class name
     * eg table_name to TableNameQuery
     * @return string
     */
    protected function getClassName()
    {
        return $this->camelCase($this->table). 'Query';
    }

    /**
     * Converts underscore separated strings to camel case
     * @param $value
     * @return string
     */
    protected function camelCase($value){
        return preg_replace_callback('/_(.?)/', function ($matches) {
                return ucfirst($matches[1]);
            }, ucfirst($value));
    }


}