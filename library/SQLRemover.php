<?php

namespace FC\SQL;

class SQLRemover extends SQLBuilderBase
{
    public function execute()
    {
        if(empty($this->table))
        {
            throw new SQLException(sprintf('%s: table missing.', get_class()));
        }

        if(count($this->conditionColumns) <= 0)
        {
            throw new SQLException(sprintf('%s: conditionColumns missing.', get_class()));
        }

        $query = sprintf('DELETE FROM %s WHERE %s', $this->table, implode(' AND ', $this->conditions()));
        $this->mysqlDB->query($query, $this->buildStmtParams());
    }
}