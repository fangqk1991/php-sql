<?php

namespace FC\SQL;

class BuilderBase
{
    protected $mysqlDB;
    protected $table;

    protected $conditionColumns = array();
    protected $conditionValues = array();

    public function __construct(MysqlDB $mysqlDB)
    {
        $this->mysqlDB = $mysqlDB;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function checkPrimaryKey($params, $key)
    {
        if(!isset($params[$key]))
        {
            throw new SQLException(sprintf('%s: primary key missing.', get_class()));
        }

        $this->addConditionKV($key, $params[$key]);
    }

    public function addConditionKV($key, $value)
    {
        array_push($this->conditionColumns, sprintf('(%s = ?)', $key));
        array_push($this->conditionValues, $value);
    }

    public function addSpecialCondition($condition, $value = NULL)
    {
        array_push($this->conditionColumns, sprintf('(%s)', $condition));
        if($value !== NULL)
        {
            array_push($this->conditionValues, $value);
        }
    }

    protected function conditions()
    {
        return $this->conditionColumns;
    }

    protected function checkTableValid()
    {
        if(empty($this->table))
        {
            throw new SQLException(sprintf('%s: table missing.', get_class()));
        }
    }

    public function buildConditionStr()
    {
        return implode(' AND ', $this->conditions());
    }

    protected function stmtValues()
    {
        return $this->conditionValues;
    }
}
