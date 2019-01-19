<?php

namespace FC\SQL;

class SQLRemover extends BuilderBase
{
    public function execute()
    {
        $this->checkTableValid();

        if(count($this->conditionColumns) <= 0)
        {
            throw new SQLException(sprintf('%s: conditionColumns missing.', get_class()));
        }

        $query = sprintf('DELETE FROM %s WHERE %s', $this->table, implode(' AND ', $this->conditions()));
        $this->database->query($query, $this->stmtValues());
    }
}