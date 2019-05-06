<?php
namespace Leo\Components\Table\Column;

/**
 * ColumnIndex displays the column index value of the Table Component
 *
 * @author bnanna
 * Date 1/7/2016
 */
class ColumnIndex {
    
    protected $table_index;

    public function setTableIndex(array $table_index)
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
        return $this->table_index['column'];
    }
}
