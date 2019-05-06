<?php

namespace Leo\Helpers;

use Leo\Db\Connect;
use Leo\Exception\DBQueryError;
use InvalidArgumentException;
use Leo\Db\ActiveRecord;
use Leo\Leo;
use PDO;

class Constructor
{

    public $type = 'select';
    /**
     *
     * @var PDO database connection object 
     */
    public $connection = null;
    protected $where = [];
    protected $order = [];
    protected $join = [];
    protected $update = [];
    protected $insert = [];
    protected $limit = [];
    protected $select = array('*');
    protected $params = [];
    protected $sql = [];
    protected $objects = [];
    public $table = [];
    protected $whereNotIn = [];
    protected $whereIn = [];
    protected $like = [];
    protected $between = [];
    protected $distinct = false;
    protected $result = array();
    protected $fetchMode;
    protected $transactionName = '';
    protected $affectedRows = null;
    protected $group = [];

    /**
     * @return int
     */
    public function affectedRows()
    {
        return $this->affectedRows;
    }

    /**
     * @param null $affectedRows
     * @return Constructor
     */
    public function setAffectedRows($affectedRows)
    {
        $this->affectedRows = $affectedRows;
        return $this;
    }



    /**
     * Resets all previously provided values
     * @return \Leo\Helpers\Constructor
     */
    public function reset()
    {
        $this->where =
        $this->update =
        $this->insert =
        $this->table =
        $this->order =
        $this->join =
        $this->limit =
        $this->objects =
        $this->like =
        $this->between =
        $this->whereNotIn =
        $this->whereIn =
        $this->params =
        $this->group =
        $this->result = array();
        $this->sql = '';
        $this->distinct = false;
        $this->type = 'select';
        $this->select = array('*');

        return $this;
    }

    /**
     * Construct the where part of the statement
     * <pre>
     * [5,2, '>'] = 5 > 2
     * [ ['user_id',2],['prod','4','>'] ] = (('user_id=2) AND (prod > 4))
     * [ ['user_id',12,'='], ['user_id',45,'<', 'OR'] ] = ( (user_id=12) OR (user_id < 45) )
     * [ ['user_id',':user_id'],['prod','4','>'] ] = (('user_id = ':user_id') AND (prod > 4))
     * </pre>
     * @param array $condition the where part of the statement
     * @param array $params [':placeholder' => value] any params to replace placeholders if used
     * @param string $op AND or OR string
     * @return \Leo\Helpers\Constructor
     */
    public function where(array $condition, array $params = array(), $op = 'AND')
    {
        if($condition) {
            $this->where[] = array($condition, $op, $params);
            $this->addParams($params);
        }

        return $this;
    }
    
    
    /**
     * Use to check if a result exists after running a select query
     * @return bool true if results exist otherwise false
     */
    public function exists()
    {
        $results = $this->select(["COUNT('*')"])->run()->getFirst();
        return !!$results[0][0];
    }
    
    /**
     * Use to get the count after running a select query
     * @return bool true if results exist otherwise false
     */
    public function count()
    {
        return $this->select(["COUNT('*')"])->run()->getFirst();
    }

    public function addParams(array $params)
    {
        if(!empty($params))
        {
            $this->params = array_merge($this->getParams(), $params);
        }
        
        return $this;
    }

    /**
     * <pre>
     * $this->order(['name' , 'desc'])
     * </pre>
     * @param array $columns
     * @return Constructor
     */
    public function order(array $columns)
    {
       
        $this->order[] = $columns;

        return $this;
    }

    /**
     * To make construct a join statement
     * <pre>
     * join('INNER JOIN', 'Customers', ['Orders.CustomerID' => 'Customers.CustomerID'])
     * </pre>
     * @param string $type
     * @param string $table
     * @param array $columns
     * @return \Leo\Helpers\Constructor
     */
    public function join($type, $table, array $columns)
    {
        
        $this->join[] = array(strtoupper($type), $table, $columns);

        return $this;
    }

    private function getJoinSql()
    {
        $join = '';
        /**
         * SELECT Orders.OrderID, Customers.CustomerName,
         * Orders.OrderDate
         * FROM Orders
         * INNER JOIN Customers
         * ON Orders.CustomerID=Customers.CustomerID; 
         */
        foreach ($this->join as $array)
        {
            $string = '';

            $join .= " {$array[0]} {$array[1]} ON ";

            foreach ($array[2] as $field1 => $field2)
            {
                $string.= " AND $field1 = $field2 ";
            }

            $join .= trim($string, 'AND OR');
        }

        return $join;
    }

    /**
     * <pre>
     * array(':userid'=>5, ':orderid'=>12')
     * </pre>
     * @param array $params
     * @return Constructor
     */
    public function params(array $params)
    {
        $this->addParams($params);

        return $this;
    }

    /**
     * TODO implement having
     * @return $this
     */
    public function having()
    {
        return $this;
    }

    /**
     * ['name', 'guest_id'] => GROUP BY name, guest_id
     * @return $this
     */
    public function groupBy(array $groupby)
    {
        $this->group[] = $groupby;
        return $this;
    }

    /**
     * [column_name, value1, value2] is converted to <br/>
     * WHERE column_name BETWEEN value1 AND value2
     * @param array $columns
     * @param string $condition AND or OR
     * @return $this
     */
    public function between(array $columns, $condition = 'AND')
    {
        $this->between[] = array($columns, $condition);

        return $this;
    }

    public function setSql($sql)
    {
        $this->setType('raw');
        
        $this->sql = (string) $sql;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * <pre>
     * [0,5] , [5]
     * </pre>
     * @param array $limit
     * @return $this
     */
    public function limit(array $limit)
    {

        $this->limit = $limit;

        return $this;
    }

    public function offset()
    {
        return $this;
    }

    

    public function table($table)
    {
        $this->table[] = $table;
        return $this;
    }

    
    /**
     * Construct the where part of the statement
     * @param array $condition the where part of the statement
     * <pre>
     * [column_name,pattern] = column LIKE pattern
     * [ ['user_id',"'%boy%'"], ['prod',"'%coke'"] ] = ((user_id LIKE '%boy%' ) AND (prod LIKE '%coke%'))
     * like([ ['user_id',':user_id'],['prod',':productname','OR']],[':user_id'=>'%boy%',':productname'=>'%bottle'], 'AND' ) 
     * = (('user_id LIKE '%boy%') OR (prod LIKE '%bottle')) AND
     * </pre>
     * @param array $params [':placeholder' => value] any params to replace placeholders if used
     * @param string $op AND or OR string
     * @return \Leo\Helpers\Constructor
     */
    public function like(array $condition, array $params = array(), $op = 'AND')
    {
        
        $this->like[] = array($condition, $op, $params);
        $this->addParams($params);

        return $this;
    }


    /**
     * [ ['user_id',12,'='], ['user_id',45,'<', 'OR'] ] = ( (user_id=12) OR (user_id < 45) )
     * @param array $conditions
     * @return string
     */
    private function whereAll(array $conditions)
    {
        $string = '';
        
        foreach($conditions as $condition)
        {
            $op = isset($condition[3]) ? $condition[3] : 'AND';
            
            $string .= $this->whereString($condition, $op);
        }
        
        $whereString = trim($string, 'AND OR');
        
        return "($whereString)";
            
    }
    
    /**
     * 
     * @param string|array $condition
     * @param string $op operand AND OR
     * @return string
     */
    private function whereString(array $condition, $op)
    {
        $string = '';
        if (is_array($condition))
        {
            $column_name = $condition[0]; //AND OR
            $operand = isset($condition[2]) ? $condition[2] : '=';
            $value = $this->getValue($condition[1], $operand);
            /**
             * The first where should have AND OR in front not before
             */
            $string = " ($column_name $value) $op ";
        }

        return $string;
    }

    /**
     * A where conditional e.g <b>(column = 2)</b> has 3 parts namely (column, operand, value)
     * This function inspects the operand and does any appropriate conversions
     * Converts null to IS NULL
     * and != null to IS NOT NULL
     * @param string $value
     * @param string $operand
     * @return string
     */
    protected function getValue($value, $operand = '')
    {
        if (is_null($value) OR strtoupper($value) == 'NULL')
        {
            if ($operand === '!=')
            {
                return 'IS NOT NULL';
            }
            else
            {
                return 'IS NULL';
            }
        }

        return "$operand $value";
    }

    protected function getOrderString()
    {
        $string = '';

        foreach ($this->order as $array)
        {
            $order = isset($array[1]) ? $array[1] : 'ASC';
            $string .= "{$array[0]} $order,";
        }

        return trim($string, ',');
    }

    protected function getGroupByString()
    {
        $string = '';

        foreach ($this->group as $group)
        {
            $string.= implode(',', $group).',';
        }

        return trim($string, ',');
    }

    /**
     * Where not in statement
     * @param string $column_name
     * @param array $fields
     * @param string $op
     * @return $this
     */
    public function whereNotIn($column_name, array $fields = [], $op = 'AND')
    {
        
        $this->whereNotIn[] = array($column_name, $fields, $op);
        return $this;
    }

    /**
     * Where in query statement
     * @param string $column_name
     * @param array $fields
     * @param string $op 'AND' or 'OR'
     * @return $this
     */
    public function whereIn($column_name, array $fields = [], $op = 'AND')
    {
        
        $this->whereIn[] = array($column_name, $fields, $op);
        return $this;
    }

    /**
     * Sets the query mode to select and also sets the columns to select
     * @param array $columns the columns to select
     * @param boolean $distinct set to true to run a distinct query
     * @return \Leo\Helpers\Constructor
     */
    public function select(array $columns = ['*'], $distinct = false)
    {
        $this->setType('select');
        $this->select  = is_array($columns) && count($columns) ? $columns : array('*');
        $this->distinct = $distinct;
        return $this;
    }

    /**
     * To insert data into the database table
     * <p>Format examples:</p>
     * <pre>
     * $this->insert([
     * 'column_name'=> 'James',
     * 'column_name'=> 12,
     * ]);
     * <p>OR</p>
     * $this->insert([
     * 'column_name'=> :firstname,
     * 'column_name'=> :user_id,
     * ], [':user_id' => 12, ':firstname'=>'James']);
     * </pre>
     * @param array $columns_values
     * @param array $params
     * @return $this
     */
    public function insert(array $columns_values, array $params = [])
    {
        $this->setType('insert');
        
        if(!count($params))
        {
            $columns_params = $this->getPdoFormat($columns_values);
            $columns_values = $columns_params['columns'];
            $this->addParams($columns_params['params']);
        }
        else
        {
            if (count($params) != count($columns_values))
            {
                throw new InvalidArgumentException(lang('Parameters count does not match columns'));
            }
            else
            {
                $this->addParams($params);
            }
        }

        $this->insert = $columns_values;

        return $this;
    }


    private function selectSql()
    {
        //SELECT columns FROM table name WHERE column operand condition ORDER BY column ASC LIMIT 0,1
        $template = "SELECT %s %s FROM %s ";
        $whereString = $this->getWhereString();
        $this->sql = sprintf($template, ($this->distinct) ? 'DISTINCT ' : '', implode(', ', $this->select), $this->table[0]);
        $this->sql .= count($this->join) ? $this->getJoinSql() : '';
        $this->sql .= ($whereString) ? ' WHERE ' . $whereString : '';
        $this->sql .= (count($this->group)) ? ' GROUP BY ' . $this->getGroupByString() : '';
        $this->sql .= (count($this->order)) ? ' ORDER BY ' . $this->getOrderString() : '';
        $this->sql .= count($this->limit) ? ' LIMIT ' . implode(',', $this->limit) : '';
    }

    private function insertSql()
    {
        //INSERT INTO table_name (column_name1, column_name2) VALUES (value1, value2, value3)
        $template = "INSERT INTO %s (%s) VALUES (%s)";
        $this->sql = sprintf($template, $this->table[0], implode(',', array_keys($this->insert)), implode(',', array_values($this->insert)));
    }

    public function delete()
    {
        $this->setType('delete');

        return $this;
    }

    private function deleteSql()
    {
        //DELETE FROM TABLE WHERE column=value;
        $template = "DELETE FROM %s";
        $whereString = $this->getWhereString();
        $this->sql = sprintf($template, $this->table[0]);
        $this->sql .= ($whereString) ? ' WHERE ' . $whereString : '';
        $this->sql .= count($this->limit) ? ' LIMIT ' . implode(',', $this->limit) : '';
    }

    private function updateSql()
    {
        $getColumnValues = function(array $column_value)
        {
            $string = '';
            foreach ($column_value as $k => $v)
            {
                $string .= "$k = $v,";
            }
            return rtrim($string, ',');
        };

        //UPDATE table_name SET column=value1, column= value2 WHERE some_column = some_value
        $template = "UPDATE %s SET %s";
        $whereString = $this->getWhereString();
        $this->sql = sprintf($template, $this->table[0], $getColumnValues($this->update));
        $this->sql .= ($whereString) ? ' WHERE ' . $whereString : '';
        $this->sql .= count($this->limit) ? ' LIMIT ' . implode(',', $this->limit) : '';
    }

    /**
     * Update are record in db
     * <pre>
     * $this->update([
     * 'firstname'=> ':firstname',
     * 'user_id'=> :user_id,
     * ], [':firstname'=>'John',':user_id' => 12]);
     * <p>OR</p>
     * $this->update([
     * 'firstname'=> 'James',
     * 'lastname'=> 'mike'
     * ])
     * </pre>
     * @param array $column_values
     * @param array $params
     * @return \Leo\Helpers\Constructor
     * @throws InvalidArgumentException
     */
    public function update(array $column_values, array $params = [])
    {
        $this->setType('update');
        if (count($params))
        {
            if (count($params) != count($column_values))
            {
                throw new InvalidArgumentException(lang('Params count don\'t match values'));
            }
            else
            {
                $this->addParams($params);

                foreach ($column_values as $column => $value)
                {
                    $column_values[$column] = $value;
                }
            }
        }

        $this->update = $column_values;

        return $this;
    }

    protected function createQuery()
    {

        switch ($this->type)
        {
            case 'select':
                $this->selectSql();
                break;
            case 'update':
                $this->updateSql();
                break;
            case 'insert':
                $this->insertSql();
            case 'raw':
                break;
            case 'delete':
                $this->deleteSql();
                break;
            default:
                throw new InvalidArgumentException('Query type ' . $this->type . ' not yet supported');
        }

    }

    /**
     * Returns the constructed string
     * @return string
     */
    public function getSql()
    {
        $this->createQuery();
        return $this->sql;
    }


    /**
     * Constructs the where part of the query
     * @return string
     */
    private function getWhereString()
    {
        $string = '';
                
        foreach ($this->where as $array)
        {
            
            if (isset($array[0][0]) && is_array($array[0][0]))
            {//multiple where conditions
                
                $s = $this->whereAll($array[0]);
                $string .= ($string) ? " {$array[1]} $s" : "$s";
                $string = rtrim($string, 'AND OR');
            }
            else
            {
                $s = $this->whereString($array[0], $array[1]);
                $string .= ($string) ? " {$array[1]} $s" : "$s";
                $string = rtrim($string, 'AND OR');
            }
        }
                        
        //WHERE not in
        //WHERE column NOT IN (fields)
        if (count($this->whereNotIn))
        {
            foreach ($this->whereNotIn as $value)
            {
                $s = "{$value[0]} NOT IN (" . implode(',', $value[1]) . ")";
                $string .= ($string) ? " {$value[2]} $s " : " $s {$value[2]} ";
            }
            
            $string = rtrim($string, 'AND OR');
        }

        if (count($this->whereIn))
        {
            foreach ($this->whereIn as $value)
            {
                $s = "({$value[0]} IN (" . implode(',', $value[1]) . "))";
                $string .= ($string) ? " {$value[2]} $s " : " $s {$value[2]} ";
            }
            $string = rtrim($string, 'AND OR');
        }

        if (count($this->between))
        {

            foreach ($this->between as $value)
            {
                $s = "({$value[0][0]} BETWEEN {$value[0][1]} AND {$value[0][2]})";
                $string .= ($string) ? " {$value[1]} $s " : " $s {$value[1]} ";
            }
            
            $string = rtrim($string, 'AND OR');
        }


        if (count($this->like))
        {

            foreach ($this->like as $value)
            {                
                if (is_array($value[0][0]))
                {//multiple like conditions
                    $s = $this->likeAll($value[0]);
                    $string .= ($string) ? " {$value[1]} $s" : "$s";
                    $string = rtrim($string, 'AND OR');
                }
                else
                {
                    $s = " ({$value[0][0]} LIKE {$value[0][1]})";
                    $string .= ($string) ? " {$value[1]} $s " : " $s {$value[1]} ";
                }
            }
        }

        return trim($string, 'AND OR');
    }
    
    private function likeAll($conditions)
    {
        $string = '';
        
        foreach ($conditions as $value)
        {
            $value[2] = isset($value[2]) ? $value[2] : '';
            $s = " ({$value[0]} LIKE {$value[1]})";
            $string .= ($string) ? " {$value[2]} $s " : " $s {$value[2]} ";
        }
                
        return '('.rtrim($string, 'AND OR').')';
    }

    /**
     * Get database connection
     * @return Connect
     */
    public function getConnection()
    {
        if(is_null($this->connection)){
            $this->connection = Leo::gc('dbConnect');
        }
        return $this->connection;
    }

    /**
     * Start a transaction
     * @param string $name
     * @return bool
     */
    public function beginTransaction($name = 'leo_transaction')
    {
        if(false == $this->inTransaction())
        {
            leo()->getLogger()->write('Starting transaction '.$name, LOG_TYPE_DEBUG);
            $this->setTransactionName($name);
            return $this->getConnection()->getPdo()->beginTransaction();
        }

        return false;

    }

    /**
     * Commit transaction
     * @return bool
     */
    public function commitTransaction()
    {
        leo()->getLogger()->write('Committing transaction '.$this->transactionName, LOG_TYPE_DEBUG);
        return $this->getConnection()->getPdo()->commit();
    }


    /**
     * Roll back a transaction
     * @param string $name transaction name
     * @return bool
     */
    public function rollBackTransaction($name = 'leo_transaction')
    {
        if($this->getTransactionName()  === $name)
        {
            leo()->getLogger()->write('Rolling back transaction '.$this->transactionName, LOG_TYPE_DEBUG);

            return $this->getConnection()->getPdo()->rollBack();
        }

        return false;
    }

    /**
     * Get any error when run query
     */
    public function getErrorInfo()
    {
        $this->getConnection()->getPdo()->errorInfo();
    }


    /**
     * In transaction
     * return bool true if transaction has started. false otherwise
     */
    public function inTransaction()
    {
        return $this->getConnection()->getPdo()->inTransaction();
    }
    
    /**
     * Last insert id after an insert is performed
     * @return mixed
     */
    public function getLastInsertId()
    {
        return $this->getConnection()->getPdo()->lastInsertId();
    }

    /**
     * Runs the query
     * @param string|null $sql raw query string
     * @return \Leo\Helpers\Constructor
     */
    public function run($sql = '')
    {
        if ($sql)
        {
            $this->setSql($sql);
        }

        $this->runQuery();

        return $this;
    }
    
    /**
     * Returns the first column value from the result set.
     * Good for results of count querys
     * @return string|int|null if results is empty
     */
    public function getFirst()
    {

        if(is_array($this->getResult()))
        {
            $array = $this->getResult();

            $array = array_shift($array);
            
            if(!is_null($array))
            {
               return current($array); 
            }
        }
        
        return null;
    }

    /**
     * @throws \Exception
     */
    private function runQuery()
    {
        try
        {
            $pdo = $this->getConnection()->getPdo();//get pdo connection

            $sql = $this->getSql();

            $params = $this->getParams();

            $domainDbConfig = $this->getConnection()->getDatabaseSettings();
            
            //optionally log db query and params based on config
            if(isset($domainDbConfig['logQueries']))
            {
               leo()->getLogger()->write($sql . ( (isset($domainDbConfig['logQueryParams']) && $domainDbConfig['logQueryParams'])
                       ? ' with params ['.  implode(',' , $params).']' : '') ) ;
            }

            $sth = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

            $query_result = count($params) ? $sth->execute($params) : $sth->execute();

            if ($this->getType() === 'select' OR
                    ($this->getType() ==='raw' && 0===stripos(ltrim($sql), 'select')))
            {//select query

                $this->affectedRows = $sth->columnCount();

                $query_result = $sth->fetchAll($this->getFetchMode());
            }
            elseif(in_array($this->getType(),['insert','update','delete']))
            {
                $this->affectedRows = $sth->rowCount();
            }

            $this->setResults($query_result);
        }
        catch (\Exception $e)
        {
            new DBQueryError($e);
            throw $e;
        }
    }
    
    public function getFetchMode()
    {
        return $this->fetchMode ?: PDO::FETCH_ASSOC;        
    }
    
    public function setFetchMode($fetchMode)
    {
        $this->fetchMode = $fetchMode;
    }
    
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Results of query run
     * @return array|int array for select querys, int for update,insert,delete
     */
    public function getResult()
    {
        return $this->result;
    }

    public function setResults($results)
    {
        $this->result = $results;
    }


    /**
     * Inserts or updates a record
     * @param ActiveRecord $model
     * @param array|null $interested_columns
     * @return Constructor
     */
    public function saveModel(ActiveRecord $model, array $interested_columns = null)
    {
        $this->params = []; //empty params in preparation for a new query

        $table_columns = $model->getTableInfo()->getColumns();
        
        $columns = is_array($interested_columns) ?
                array_intersect($interested_columns, $table_columns) //extract existing columns
                :
                $table_columns;

        $insertData = $this->getColumnsToBeInserted($model, $columns);

        if ($model->getIsNewRecord())
        {
            return $this->insert($insertData['columns'], $insertData['params'])->run();
        }
        else
        {

            return $model->getPrimaryColumn()
                    ?
                    $this->update($insertData['columns'], $insertData['params'])
                    ->where([$model->getPrimaryColumn(),$model->getPrimaryKey()])
                    ->run()
                    :
                $this->updateRecordWithNoPrimaryKey($model);
        }
    }

    /**
     * No supported yet
     * Handles update statements constructs for models with no primary keys or multiple primary keys
     * @param ActiveRecord $model
     * @throws \Exception
     */
    private function updateRecordWithNoPrimaryKey($model)
    {
        throw new \Exception(get_class($model). ' has no primary key');
    }

    /**
     * Ensures the columns and their data are in the right format ready for inserting
     * @param \Leo\Db\ActiveRecord $model
     * @param array $columns columns to be inserted or updated in the model table
     * @return array
     */
    protected function getColumnsToBeInserted(ActiveRecord $model, array $columns)
    {
        if ($model->getIsNewRecord())
        {//remove primary key if included as that is auto incremented
            $primary_column = $model->getTableInfo()->getPrimaryColumn();
            $primary_column_key = array_search($primary_column, $columns);
            if ($primary_column_key !== FALSE)
            {
                unset($columns[$primary_column_key]);
            }
        }

        $property_values = $model->getProperties($columns);

        //remove empty values
        foreach ($property_values as $property => $property_value)
        {
            if (empty($property_value) || is_null($property_value))
            {
                unset($property_values[$property]);
            }
        }

        return $this->getPdoFormat($property_values);
    }

    //string
    public function stringify($str)
    {
        return "'$str'";
    }

    /**
     * Converts an array of column values to prepared statement format
     * <pre>
     * array('firstname'=>'Peter', 'lastname'=>'Josh')
     * <br/>
     * array( 
     *  array('firstname'=>':firstname', 'lastname'=>':lastname'), 
     *  array(':firstname'=>'Peter', ':lastname'=>'Josh') 
     * )
     * </pre>
     * @param array $column_values column value pairs
     * @return array array('params=>[':firstname'=>'John'], 'columns'=>['firstname'=>':firstname'])
     */
    public function getPdoFormat(array $column_values)
    {
        $columns = $params = array();

        foreach ($column_values as $key => $value)
        {
            $placeholder = ":$key";
            $columns['`'.$key.'`'] = $placeholder;
            $params[$placeholder] = $value;
        }

        return array('params' => $params, 'columns' => $columns);
    }
    
    /**
     * Did the query return any result
     * @return bool
     */
    public function hasResults()
    {
        return count($this->getResult());
    }
    
    /**
     * Instantiates the model's properties with the values loaded from the database query if any
     * @param \Leo\Db\ActiveRecord $class
     * @param bool $throwException Should an exception be thrown if column does not exist in model
     * @return null|\Leo\Db\ActiveRecord return instance of class or null if query returns no result
     */
    public function loadClass(ActiveRecord $class, $throwException = true)
    {
        if(!$this->hasResults())
        {
            return null;
        }

        return static::instantiate(current($this->getResult()), $class);

    }
    
    /**
     * Return array of found rows are instantiated Model objects
     * @param ActiveRecord $class
     * @return array or empty array if nothing found
     */
    public function loadAll(ActiveRecord $class)
    {
        $rows = [];
                
        foreach($this->getResult() as $row)
        {
            $rows[] = static::instantiate($row, $class);
        }
        
        return $rows;
    }



    /**
     * Instantiate a class with db values
     * @param array $rows records from db
     * @param ActiveRecord $class
     * @return ActiveRecord
     */
    public static function instantiate($rows, ActiveRecord $class)
    {
        $cloned_class = clone $class;

        foreach ($rows as $property => $value)
        {
            if(is_int($property))
            {//when fetch mode is both
                continue;
            }

            if($cloned_class->hasProperty($property))
            {
                $cloned_class->{'set'.$property}($value);
            }
            
        }

        $cloned_class->setIsNewRecord(false);
        
        return $cloned_class;
    }


    /**
     * GEt transaction name
     * @return string
     */
    public function getTransactionName()
    {
        return $this->transactionName;
    }

    /**
     * Set transaction name
     * @param string $transactionName
     * @return Constructor
     */
    public function setTransactionName($transactionName)
    {
        $this->transactionName = $transactionName;
        return $this;
    }
       
    
}
