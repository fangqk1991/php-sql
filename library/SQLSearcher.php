<?php

namespace FC\SQL;

class SQLSearcher extends BuilderBase
{
    private $queryColumns = array();

    private $distinct = FALSE;
    private $page = -1;
    private $feedsPerPage = 1;

    private $optionStr;

    public function markDistinct()
    {
        $this->distinct = TRUE;
    }

    public function setColumns(array $columns)
    {
        $this->queryColumns = $columns;
    }

    public function addColumn($column)
    {
        array_push($this->queryColumns, $column);
    }

    public function setPageInfo($page, $feedsPerPage)
    {
        $this->page = $page;
        $this->feedsPerPage = $feedsPerPage;
    }

    public function setOptionStr($optionStr)
    {
        $this->optionStr = $optionStr;
    }

    private function checkColumnsValid()
    {
        if(count($this->queryColumns) <= 0)
        {
            throw new SQLException(sprintf('%s: queryColumns missing.', get_class()));
        }
    }

    public function export()
    {
        $this->checkTableValid();
        $this->checkColumnsValid();

        $query = sprintf('SELECT %s %s FROM %s', $this->distinct ? 'DISTINCT' : '', implode(', ', $this->queryColumns), $this->table);

        $conditions = $this->conditions();
        if(count($conditions) > 0)
        {
            $query = sprintf('%s WHERE %s', $query, $this->buildConditionStr());
        }

        return [$query, $this->stmtValues()];
    }

    public function queryList()
    {
        list($query, $params) = $this->export();

        if($this->optionStr !== NULL)
        {
            $query = sprintf('%s %s', $query, $this->optionStr);
        }

        if($this->page >= 0 && $this->feedsPerPage > 0)
        {
            $query = sprintf('%s LIMIT %d, %d', $query, $this->page * $this->feedsPerPage, $this->feedsPerPage);
        }

        return $this->mysqlDB->query($query, $params);
    }

    public function queryCount()
    {
        $this->checkTableValid();

        if($this->distinct)
        {
            $query = sprintf('SELECT COUNT(DISTINCT %s) AS count FROM %s', implode(', ', $this->queryColumns), $this->table);
        }
        else
        {
            $query = sprintf('SELECT COUNT(*) AS count FROM %s', $this->table);
        }

        $conditions = $this->conditions();
        if(count($conditions) > 0)
        {
            $query = sprintf('%s WHERE %s', $query, $this->buildConditionStr());
        }

        $result = $this->mysqlDB->query($query, $this->stmtValues());
        return $result[0]['count'];
    }
}