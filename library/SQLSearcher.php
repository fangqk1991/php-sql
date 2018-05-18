<?php

namespace FC\SQL;

class SQLSearcher extends SQLBuilderBase
{
    private $queryColumns = array();

    private $page = -1;
    private $feedsPerPage = 1;

    private $optionStr;

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

    public function queryList()
    {
        $this->checkTableValid();
        $this->checkColumnsValid();

        $query = sprintf('SELECT %s FROM %s', implode(', ', $this->queryColumns), $this->table);

        $conditions = $this->conditions();
        if(count($conditions) > 0)
        {
            $query = sprintf('%s WHERE %s', $query, $this->buildConditionStr());
        }

        if($this->optionStr !== NULL)
        {
            $query = sprintf('%s %s', $query, $this->optionStr);
        }

        if($this->page >= 0 && $this->feedsPerPage > 0)
        {
            $query = sprintf('%s LIMIT %d, %d', $query, $this->page * $this->feedsPerPage, $this->feedsPerPage);
        }

        return $this->mysqlDB->query($query, $this->buildStmtParams());
    }

    public function queryCount()
    {
        $this->checkTableValid();

        $query = sprintf('SELECT %s FROM %s', 'COUNT(*) AS count', $this->table);

        $conditions = $this->conditions();
        if(count($conditions) > 0)
        {
            $query = sprintf('%s WHERE %s', $query, $this->buildConditionStr());
        }

        $result = $this->mysqlDB->query($query, $this->buildStmtParams());
        return $result[0]['count'];
    }
}