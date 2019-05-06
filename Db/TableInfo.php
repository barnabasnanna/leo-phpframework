<?php

namespace Leo\Db;

use Exception;
use PDO;
use PDOException;
use Leo\Leo;


/**
 * TableInfo class provides information about a table associated with model
 *
 * @author barnabasnanna
 */
class TableInfo
{

    protected $name;
    protected $columns = array();
    protected $_data_ = array();
    protected $primary_column;
    protected $connection = null;
    protected $cache_location = null;



    /**
     * Return the columns of a database table
     * @return array
     */
    public function getColumns()
    {
        if (empty($this->columns))
        {
            $this->columns = $this->fetchColumns();
        }
        return $this->columns;
    }

    /**
     * Prepends table name to every column name.
     * Useful when running join queries on table with similar column names
     * @param array $interestedColumns
     * @return array
     */
    public function getPrefixedColumns($interestedColumns = [])
    {
        return array_map(function($column) use ($interestedColumns){

            if(count($interestedColumns))
            {
                if (!in_array($column, $interestedColumns))
                {
                    return '';
                }
                return '`' . $this->getName() . '`.`' . $column . '`';
            }
            else
            {
                return '`' . $this->getName() . '`.`' . $column . '`';
            }
        }, $this->getColumns());
    }

    /**
     * Convert prefixed names to a string.
     * Useful when running join queries on table with similar column names
     * @param array $interestedColumns Return only columns you are interested in
     * @return string
     */
    public function getColumnsString(array $interestedColumns = [])
    {
        //replace comma at end and in between
        return preg_replace('/,+/' ,',',
            trim(implode(',', $this->getPrefixedColumns($interestedColumns)),',')
            );
    }

    /**
     * Get the columns of a table
     * @return array
     */
    private function fetchColumns()
    {
        return array_keys($this->getData());
    }
    
    private function setData(array $table_data)
    {
        if($this->cache_location && file_exists($this->cache_location))
        {
            file_put_contents($this->cache_location.DS.$this->getName(), serialize($table_data));
        }

        $this->_data_ = $table_data;
    }


    /**
     * Get the table columns data
     * @return array
     */
    public function getData()
    {
        $returnValue = [];

        //check table cache
        if(is_dir($this->cache_location) && file_exists($this->cache_location.DS.$this->getName()))
        {
            $returnValue = unserialize(file_get_contents($this->cache_location.DS.$this->getName()));
        }
        else
        {//no cache support

            if(!empty($this->_data_))
            {
                $returnValue = $this->_data_;
            }
            else
            {
                $returnValue = $this->updateTableData();
            }
        }

        return $returnValue;
    }

    /**
     * Update the table data in
     * @return array
     * @throws Exception
     */
    public function updateTableData(){
        $table_data = array();

        $sql = "SELECT COLUMN_NAME,DATA_TYPE,COLUMN_KEY,COLUMN_COMMENT,"
            . "CHARACTER_MAXIMUM_LENGTH,IS_NULLABLE"
            . " FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :table AND 
                 table_schema IN (SELECT DATABASE()) ORDER BY ORDINAL_POSITION ASC";

        try
        {

            $stmt = $this->getConnection()->getPdo()->prepare($sql);
            $stmt->bindValue(':table', $this->getName(), PDO::PARAM_STR);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $table_data[$row['COLUMN_NAME']] = [];
                $table_data[$row['COLUMN_NAME']]['name'] = $row['COLUMN_NAME'];
                $table_data[$row['COLUMN_NAME']]['type'] = $row['DATA_TYPE'];
                $table_data[$row['COLUMN_NAME']]['key'] = $row['COLUMN_KEY'];
                $table_data[$row['COLUMN_NAME']]['comments'] = $row['COLUMN_COMMENT'];
                $table_data[$row['COLUMN_NAME']]['max_length'] = $row['CHARACTER_MAXIMUM_LENGTH'];
                $table_data[$row['COLUMN_NAME']]['nullable'] = $row['IS_NULLABLE'];
            }

            $this->setData($table_data);

        } catch (PDOException $pe) {
            // TODO Database connection error handler
            trigger_error('Could not connect to MySQL database. ' . $pe->getMessage(), E_USER_ERROR);
        } catch (Exception $ex) {
            Leo::log($ex->getMessage(), LOG_TYPE_APP_ERROR);
            $table_data = [];
        }

        return $table_data;
    }

    public function setColumns($tableColumns)
    {
        $this->columns = $tableColumns;
        return $this;
    }


    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * Get the primary column of model's table. This only returns the first primary key it finds
     * Doesn't support multiple primary keys tables
     * @return string | null column name or null if none
     */
    public function getPrimaryColumn()
    {
        if(!$this->primary_column)
        {
            foreach($this->getData() as $column_name => $column_data)
            {
                if( \strtolower($column_data['key']) === 'pri')
                {
                    $this->primary_column = $column_name;
                    break;
                }
            }
        }
            
        return $this->primary_column;
    }
    
    public function setPrimaryColumn($primary_column)
    {
        $this->primary_column = $primary_column;
        return $this;
    }

    /**
     * Get database connection
     * @return Connect
     */
    public function getConnection()
    {
        if(is_null($this->connection)){
            $this->connection = Leo::gc('dbConnect');
            $this->database = $this->connection->getDatabase();
        }
        return $this->connection;
    }
    
    public function setConnection(Connect $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return null
     */
    public function getCacheLocation()
    {
        return $this->cache_location;
    }

    /**
     * @param null $cache_location
     * @return TableInfo
     */
    public function setCacheLocation($cache_location)
    {
        $this->cache_location = $cache_location;
        return $this;
    }

}
