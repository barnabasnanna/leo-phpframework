<?php
namespace Leo\Components\Table;

use Leo\Db\ActiveRecord;

/**
 * Description of Table
 *
 * @author barnabasnanna
 * Date: 21/06/2016
 */
class Item extends \Leo\ObjectBase
{
    /**
     * Columns you want displayed
     * @var array
     */
    public $columns = [];
    /**
     * Active record
     * @var ActiveRecord
     */
    public $model = null;

    /**
     * classes applied to table tag. They are prefixed with table-
     * @var array
     */
    public $tableClass = [];
    /**
     * Database records
     * @var array
     */
    public $row = [];

    /**
     * Should the table be a responsive one
     * @var bool
     */
    public $responsive = true;

    /**
     * @var string
     */
    public $template = '<table class="%s"><thead><tr>%s</tr></thead><tbody>%s</tbody></table>';

    /**
     * @var int
     */
    public $row_index = 0;

    /**
     * @var int
     */
    public $column_index = 0;

    public function display()
    {
        $table = sprintf($this->template,
            $this->getTableClass(),
            $this->getHeaderColumns(),
            $this->getBodyColumns());

        if($this->responsive)
        {
            $table='<div class="table-responsive">'.$table.'</div>';
        }

        return $table;
    }

    protected function getTableClass()
    {
        $table_class = 'table ';

        foreach($this->tableClass as $class)
        {
            $table_class.=' table-'.$class;
        }

        return $table_class;
    }

    public function setModel(ActiveRecord $model)
    {
        $this->model = $model;
    }

    /**
     * Interested columns to be displayed
     * @return array
     */
    protected function getColumns()
    {

        return count($this->columns)
            ?
            array_keys($this->columns)
            :
            $this->model->getColumns();
    }

    protected function getHeaderColumns()
    {
        $header_columns = [];

        foreach(array('','Value') as $column)
        {
            $header_columns[]='<th>'.lang($column).'</th>';
        }

        return join('',$header_columns);
    }

    /**
     * Get the column table header text
     * @param string $column
     * @return string
     */
    protected function getColumnHeaderText($column)
    {
        if(isset($this->columns[$column]) &&
            is_array($this->columns[$column]) &&
            isset($this->columns[$column]['header']))
        {
            return $this->columns[$column]['header'];
        }

        return $this->model->getPropertyLabel($column);
    }


    protected function getBodyColumns()
    {
        $table_rows = [];


            //increase row counter
            ++$this->row_index;
            $this->column_index =0;
            foreach($this->getColumns() as $column)
            {
                //increase column counter
                ++$this->column_index;

                if(is_numeric($column))
                {
                    $value= $this->getColumnArrayValue($this->row, $column);
                }
                elseif(is_string($column))
                {
                    $value= $this->getColumnValue($this->row, $column);
                }

                $table_rows[] = '<tr><td>'.$this->getColumnHeaderText($column).'</td><td>'.$value.'</td></tr>';


            }



        return join('', $table_rows);
    }

    protected function columnKeyCheck($column, $column_key)
    {
        return isset($this->columns[$column]) &&
        is_array($this->columns[$column])
        && isset($this->columns[$column][$column_key]);
    }

    protected function getColumnArrayValue($row, $column)
    {
        $value = '';

        try
        {
            $class = leo()->getDi()->getClass($this->columns[$column]['column']);

            if(\method_exists($class, 'setTableIndex'))
            {//if the class requires to know the table indexes
                $class->setTableIndex(array('row'=>$this->row_index,'column'=>$this->column_index));
            }

            $value = $class->run($row);

        }
        catch (\Exception $ex)
        {
            $value = $ex->getMessage();
        }

        return $value;
    }

    /**
     * Return the value of the
     * @param object $row
     * @param string $column
     * @return value
     */
    protected function getColumnValue($row,$column)
    {
        if($this->columnKeyCheck($column, 'value'))
        {
            $value = $this->columns[$column]['value'];
        }
        else
        {
            $value = $row->getPropertyValue($column,false);
        }


        if($this->columnKeyCheck($column,'method') && \method_exists($row, $this->columns[$column]['method']))
        {
            $method_name= $this->columns[$column]['method'];

            $value = isset($value) ? $row->{$method_name}($value) : $row->{$method_name}();
        }


        if($this->columnKeyCheck($column,'format'))
        {
            $value = $this->formatValue($this->columns[$column]['format'], $value);
        }

        return $value;

    }

    /**
     * Format value with based on format configuration
     * @param \Closure $format_config
     * @param mixed $value
     * @return mixed
     */
    protected function formatValue($format_config, $value)
    {
        if ($format_config instanceof \Closure)
        {
            $value = $format_config($value);
        }
        else
        {
            $formatter = leo()->getFormatter();

            $value = $formatter->run($format_config,$value);
        }

        return $value;
    }

    /**
     * Is table responsive
     * @param bool $responsive
     */
    public function setResponsive($responsive)
    {
        $this->responsive = $responsive;
    }

}
