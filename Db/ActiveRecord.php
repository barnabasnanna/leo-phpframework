<?php

namespace Leo\Db;

use Leo\Leo;
use Leo\MainModel;
use ReflectionClass;

/**
 * ActiveRecord is the parent class for database classes
 *
 * @author Barnabas
 */
class ActiveRecord extends MainModel
{

    /**
     * @var \Leo\Helpers\Constructor
     */
    protected $constructor;
    protected $table_info = null;
    protected $isNewRecord = true;
    const STATUS_ACTIVE = 0;
    const STATUS_SUSPENDED = 1;
    const STATUS_PREVIEW = 3;
    const STATUS_DELETED =2;



    public static function getTableName()
    {
        $rc = new ReflectionClass(get_called_class());
        return \strtolower($rc->getShortName());
    }

    /**
     * Returns the class that constructs and run database queries
     * @return \Leo\Helpers\Constructor
     */
    public function getDb()
    {
        $tableName = static::getTableName();

        if (is_null($this->constructor))
        {
            $this->constructor = Leo::lc('queryConstructor', array('table' => array($tableName)));
        }

        return $this->constructor;
    }

    /**
     * This is run before the save method is called.
     * return true to continue the save action or false to halt
     * @return boolean
     */
    protected function beforeSave()
    {
        return true;
    }

    /**
     * This method is called after a successful save(update or insert) action is performed
     * <p>
     * Can run or attach events here for example after a user has been successfully registered
     * </p>
     * Note: Newly created records are still marked as new until afterSave()
     */
    protected function afterSave()
    {

    }

    /**
     * This is called before a model is deleted. Return false if you want to prevent delete
     * else return true from any overriding function
     * @return bool
     */
    protected function beforeDelete()
    {
        return true;
    }

    /**
     * This is called after a model has been successfully deleted
     */
    protected function afterDelete()
    {

    }

    /**
     * Deletes a model from database
     * @return bool
     */
    public function delete()
    {

        if($this->beforeDelete())
        {
             if(leo()->getDb()->table($this->getTableInfo()->getName())
                ->where([$this->getPrimaryColumn(), $this->getPrimaryKey()])
                ->limit([1])
                ->delete()->run()->affectedRows())
             {
                 $this->afterDelete();
                 return true;
             }
        }

        return false;
    }

    /**
     * Saves a model to database. If a new model, insert is performed else an update is done.
     *
     * @param bool $runValidation should validation be performed before save
     * @param array $columns_to_save columns to save. if null all model table fields are saved
     * @return boolean return true if operation was successful. false otherwise
     */
    public function save($runValidation = true, array $columns_to_save = null)
    {
        if ($this->beforeSave())
        {
            if ($runValidation)
            {
                $this->validate();
            }

            if (!$this->hasErrors())
            {
                if(!$this->getDb()->saveModel($this, $columns_to_save))
                {
                   return false;
                }
                else
                {
                    if($this->getIsNewRecord())
                    {
                        $this->setProperty(
                            $this->getPrimaryColumn(),
                            $this->getDb()->getlastInsertId(),
                            false
                            );
                    }

                    $this->afterSave();

                    $this->setIsNewRecord(false);

                }

            }
        }

        return !$this->hasErrors();
    }

    /**
     * Returns the value of the primary column
     * @return mixed
     */
    public function getPrimaryKey()
    {
        return $this->getPropertyValue($this->getPrimaryColumn());
    }

    /**
     * Sets the value of the primary column of model
     * @param string $value
     */
    public function setPrimaryKey($value)
    {
        if($this->getPrimaryColumn())
        {
            $this->setProperty($this->getPrimaryColumn(), $value);
        }
    }

    /**
     * Returns the primary column name of model's table
     * @return string
     */
    public function getPrimaryColumn()
    {
       return $this->getTableInfo()->getPrimaryColumn();
    }

    /**
     * Returns a class which provides various information about the table the
     * model is associated with
     *
     * @return \Leo\Db\TableInfo
     */
    public function getTableInfo()
    {
        if(is_null($this->table_info))
        {
            $this->table_info = Leo::lc('tableInfo', array('name' => array(static::getTableName())));
        }

        return $this->table_info;
    }

    /**
     * Returns the columns of the database table model is related to
     * @return array
     */
    public function getColumns()
    {
        return $this->getTableInfo()->getColumns();
    }

    /**
     * Return an array of model's db names as keys and values as values
     * @return array
     */
    public function getColumnValues()
    {
        return $this->getProperties($this->getColumns());
    }

    /**
     * Set the class that handles table schema information
     * @param TableInfo|null $table_info
     * @return ActiveRecord
     */
    public function setTableInfo(TableInfo $table_info=null): static
    {
        $this->table_info = $table_info;
        return $this;
    }

    /**
     *
     * @return boolean true if model is a new record
     */
    public function getIsNewRecord()
    {
        return $this->isNewRecord;
    }

    /**
     * Set the model is a new record
     * @param bool $value
     * @return \Leo\Db\ActiveRecord
     */
    public function setIsNewRecord($value)
    {
        $this->isNewRecord = $value;
        return $this;
    }



    /**
     * Load a model by it's attributes
     * @param array $attributes
     * @param array $selected_columns
     * @return self | null
     */
    public static function getByAttribute(array $attributes,array $selected_columns = ['*'], array $orderBy = [])
    {
        $db = leo()->getDb();

        $column_params = $db->getPdoFormat(
                $attributes
        );

        $query = $db->table(static::getTableName())->select($selected_columns);

        foreach ($orderBy as $order) {

            if(is_array($order))
            {
                $query->order($order);
            }
        }

        //create where conditional of sql statement
        foreach($column_params['columns'] as $column_name=>$placeholder)
        {
           $query->where([$column_name,$placeholder], [$placeholder=>$column_params['params'][$placeholder]]);
        }

        $query->limit([1]);

        return $query->run()->loadClass(new static());
    }

    /**
     * Load a model using the primary key value
     * @param integer $id primary key of model
     * @param array $select_columns columns you want selected
     * @return static object with keys prepopulated with db values or null if not found
     * @throws \Exception
     */
    public static function load(int $id, array $select_columns = null)
    {
        /**
         * @var $tableInfo TableInfo
         */
        $tableInfo = Leo::lc('tableInfo', array('name' => array(static::getTableName())));
        return leo()->getDb()->table(static::getTableName())
                ->where([$tableInfo->getPrimaryColumn(),$id])
                ->select( $select_columns?:$tableInfo->getPrefixedColumns())->run()->loadClass(new static());

    }

    /**
     * Load a model using the primary key value
     * @param integer $id primary key of model
     * @param array $select_columns columns you want selected
     * @return array
     */
    public static function loadAsArray($id, array $select_columns = null)
    {
        /**
         * @var $tableInfo TableInfo
         */
        $tableInfo = Leo::lc('tableInfo', array('name' => array(static::getTableName())));
        return leo()->getDb()->table(static::getTableName())
            ->where([$tableInfo->getPrimaryColumn(),intval($id)])
            ->select( $select_columns?:$tableInfo->getPrefixedColumns())->run()->getResult();

    }

    /**
     * @param array $attributes
     * @param array $select_columns
     * @param array $orderBy array( array('column','DESC'), array('column','ASC') )
     * @return array
     */
    public static function getAllByAttribute(array $attributes, $select_columns = [], $orderBy=[])
    {
        $db = leo()->getDb();

        $column_params = $db->getPdoFormat(
                $attributes
        );

        if(false==$select_columns)
        {
            /**
             * @var $tableInfo TableInfo
             */
            $tableInfo = Leo::lc('tableInfo', array('name' => array(static::getTableName())));
            $select_columns = $tableInfo->getPrefixedColumns();
        }

        $query = $db->table(static::getTableName())->select($select_columns);

        foreach ($orderBy as $order) {

            if(is_array($order))
            {
                $query->order($order);
            }
        }

        //create where conditional of sql statement
        foreach($column_params['columns'] as $column_name=>$placeholder)
        {
           $query->where([$column_name,$placeholder], [$placeholder=>$column_params['params'][$placeholder]]);
        }

        return $query->run()->loadAll(new static());
    }

    /**
     * Returns the amount of records in table
     * @return bool
     */
    public static function count()
    {
        return leo()->getDb()->table(static::getTableName())
            ->count();
    }

    /**
     * Get all records from the database of model
     * @param array|null $select_columns
     * @param array $limit
     * @return array
     */
    public static function getAll(array $select_columns = [], array $limit = [500])
    {
        /**
         * @var $tableInfo TableInfo
         */
        $tableInfo = Leo::lc('tableInfo', array('name' => array(static::getTableName())));

        if(false==$select_columns)
        {
            $select_columns = $tableInfo->getPrefixedColumns();
        }

        return leo()->getDb()->table(static::getTableName())
            ->select($select_columns)
            ->limit($limit)
            ->order([$tableInfo->getPrimaryColumn(),'DESC'])
            ->run()
            ->loadAll(new static());
    }

    /**
     * Return the properties and values of matching database columns of the model
     * @param bool $throwException should and error be thrown if a table column is not a property of class
     * @return array
     */
    public function getDbProperties($throwException = false)
    {
        return $this->getProperties(
            $this->getDbColumns(), $throwException);
    }

    /**
     * Get the database columns of a model
     * @return array
     */
    public function getDbColumns()
    {
        return $this->getTableInfo()->getColumns();
    }

    /**
     * Prepends table name to every column.
     * Useful when running join queries on table with similar column names
     * @param array $interestedColumns Return only columns you are interested in
     * @return string
     */
    public function getDbColumnsString(array $interestedColumns = [])
    {
       return $this->getTableInfo()->getColumnsString($interestedColumns);
    }

    /**
     * Prepends table name to every column.
     * Useful when running join queries on table with similar column names
     * @param array $interestedColumns Return only columns you are interested in
     * @return array
     */
    public function getPrefixedColumns(array $interestedColumns = [])
    {
        return $this->getTableInfo()->getPrefixedColumns($interestedColumns);
    }

    /**
     * Return the short name of class
     * @return string
     */
    public function getShortName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

}
