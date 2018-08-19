<?php

namespace FC\SQL;

class SQLModifier extends BuilderBase
{
    private $updateColumns = array();
    private $updateValues = array();

    public function updateKV($key, $value)
    {
        array_push($this->updateColumns, sprintf('%s = ?', $key));
        array_push($this->updateValues, $value);
    }

    public function execute()
    {
        $this->checkTableValid();

        if(count($this->updateColumns) <= 0)
        {
            return ;
        }

        if(count($this->conditionColumns) <= 0)
        {
            throw new SQLException(sprintf('%s: conditionColumns missing.', get_class()));
        }

        $query = sprintf('UPDATE %s SET %s WHERE %s', $this->table, implode(', ', $this->updateColumns), implode(' AND ', $this->conditions()));
        $this->mysqlDB->query($query, $this->stmtValues());
    }

    protected function stmtValues()
    {
        return array_merge($this->updateValues, $this->conditionValues);
    }
}
