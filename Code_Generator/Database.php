<?php

namespace Leo\Code_Generator;

use Leo\Leo;
use Leo\MainModel;

/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 10/06/2017
 * Time: 17:11
 */
class Database extends MainModel
{
    public $database;

    /**
     * @var bool Should existing classes be skipped instead of throwing error
     */
    protected $overrideExistingFiles = false;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function createModels()
    {
        $tables = $this->fetchAllTables();

        if(is_array($tables)) {

            foreach ($tables as $table) {

                //create model
                $modelQuery = new ModelQuery(['table'=>$table]);

                $modelQuery->override = $this->overrideExistingFiles;

                $modelQuery->createClassFile();

            }
        }
        else {
            //database doesnt not exist or doesnt have tables
            Leo::log(sprintf('%s does not exist or no tables added yet', $this->database), LOG_TYPE_APP_ERROR);
            die;
        }
    }

    public function fetchAllTables()
    {
        $sql = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '{$this->database}'";

        $row =  leo()->getDb()->run($sql)->getResult();

        return array_column($row, 'TABLE_NAME');

    }

    public function setSkipExisting($value = false)
    {
        $this->skipExisting = $value;
        return $this;
    }

}