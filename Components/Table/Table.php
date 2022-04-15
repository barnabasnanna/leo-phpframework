<?php
namespace Leo\Components\Table;

use Leo\Db\ActiveRecord;

/**
 * *
 * Description of Table
 *
echo (new Table(array(
    'model' => new Supplier(),
    'rows' => $businesses,
    'tableClass'=>['bordered','hover'],
    'columns' => array(
        0 => [
            'column' => [
                '_class_' => \Leo\Leo::getComponent('rowIndex')
            ],
            'header' => 'Index'
        ],
        'business_name' => [],
        'about_business' => [
            'format' => function ($value) {
                return mb_substr($value, 0, 100) . (mb_strlen($value) > 100 ? '...' : '');
            }
        ],
        'status' => ['format' => 'boolean', 'header' => lang('supplier_dashboard_table_header_approved')],
        'created_date' => ['format' => 'date'],
        [
            'header' => 'Actions',
            'column' => array(
                '_class_' => 'Leo\Components\Table\Column\Link',
                'links' => array(
                    [
                       'options'=>['class'=>'btn btn-sm btn-primary'],
                        'href' => '/supplier/business/{seller_id}',
                        'text' => lang('supplier_table_actions_view')
                    ]
                )
            ),
        ]
    ),
)))->display();
 *
 * @author barnabasnanna
 * Date: 21/06/2016
 */
class Table extends \Leo\ObjectBase
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
     *
     * @var type
     */
    public $pager = null;

    public $row_amount = 10;
    /**
     * classes applied to table tag. They are prefixed with table-
     * @var array
     */
    public $tableClass = [];
    /**
     * Database records
     * @var array
     */
    public $rows = [];

    /**
     * Should the table be a responsive one
     * @var bool
     */
    public $responsive = true;

    /**
     * @var string
     */
    public $template = '<table class="%s"><thead>%s</thead><tbody>%s</tbody></table>';

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
     * Interested columns to be displayed. If columns are not provided, the ActiveRecord columns are used
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

        foreach($this->getColumns() as $column)
        {
            $header_columns[]='<th>'.$this->getColumnHeaderText($column).'</th>';
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


        if (is_array($this->rows)) {
            foreach ($this->rows as $row) {

                $columns = [];

                //increase row counter
                ++$this->row_index;
                $this->column_index = 0; //reset

                foreach ($this->getColumns() as $column) {
                    //increase column counter
                    ++$this->column_index;

                    if (is_numeric($column)) {
                        $columns[] = $this->getColumnArrayValue($row, $column);
                    } elseif (is_string($column)) {
                        $columns[] = $this->getColumnValue($row, $column);
                    }
                }

                $table_rows[] = '<tr><td>' . implode('</td><td>', $columns) . '</td></tr>';
            }
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
     * @param ActiveRecord $row
     * @param string $column
     * @return mixed
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

        //if method exists use it to evaluate the value
        if($this->columnKeyCheck($column,'method') && \method_exists($row, $this->columns[$column]['method']))
        {
            $method_name= $this->columns[$column]['method'];
            $value = isset($value) ? $row->{$method_name}($value) : $row->{$method_name}();
        }

        //pass through formatter if key specified
        if($this->columnKeyCheck($column,'format'))
        {
            $value = $this->formatValue($column,$this->columns[$column]['format'], $value);
        }

        return $value;

    }

    /**
     * Format value with set format configuration
     * @param string $column
     * @param \Closure $format_config
     * @param mixed $value
     * @return mixed formatter value
     */
    protected function formatValue($column, $format_config, $value)
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
