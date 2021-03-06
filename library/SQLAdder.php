<?php

namespace FC\SQL;

class SQLAdder extends BuilderBase
{
    private $insertKeys = array();
    private $insertValues = array();

    public function insertKV($key, $value)
    {
        array_push($this->insertKeys, $key);
        array_push($this->insertValues, $value);
    }

    public function execute()
    {
        $this->checkTableValid();

        if(count($this->insertKeys) <= 0)
        {
            throw new SQLException(sprintf('%s: insertKeys missing.', get_class()));
        }

        $query = sprintf('INSERT INTO %s(%s) VALUES(%s)', $this->table, implode(', ', $this->insertKeys), $this->marksOfInsertQuery());
        $this->database->query($query, $this->stmtValues());

        return $this->database->lastInsertID();
    }

    private function marksOfInsertQuery()
    {
        $marks = array();

        for($i = 0, $count = count($this->stmtValues()); $i < $count; ++$i)
        {
            array_push($marks, '?');
        }

        return implode(', ', $marks);
    }

    protected function stmtValues()
    {
        return $this->insertValues;
    }
}
