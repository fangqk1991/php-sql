<?php

namespace FC\SQL;

class SQLSearcher extends BuilderBase
{
    private $_queryColumns = array();

    private $_distinct = FALSE;
    private $_page = -1;
    private $_feedsPerPage = 1;

    private $_optionStr;

    private $_orderRules = [];

    public function markDistinct()
    {
        $this->_distinct = TRUE;
    }

    public function setColumns(array $columns)
    {
        $this->_queryColumns = $columns;
    }

    public function addColumn($column)
    {
        array_push($this->_queryColumns, $column);
    }

    public function addOrderRule($sortKey, $direction)
    {
        if(!preg_match('#^(\w+)$#', $direction))
        {
            return ;
        }

        $direction = strtoupper($direction);
        if($direction !== 'DESC')
        {
            $direction = '';
        }

        array_push($this->_orderRules, ['sort_key' => $sortKey, 'sort_direction' => $direction]);
    }

    public function setPageInfo($page, $feedsPerPage)
    {
        $this->_page = $page;
        $this->_feedsPerPage = $feedsPerPage;
    }

    public function setOptionStr($optionStr)
    {
        $this->_optionStr = $optionStr;
    }

    private function checkColumnsValid()
    {
        if(count($this->_queryColumns) <= 0)
        {
            throw new SQLException(sprintf('%s: _queryColumns missing.', get_class()));
        }
    }

    public function export()
    {
        $this->checkTableValid();
        $this->checkColumnsValid();

        $query = sprintf('SELECT %s %s FROM %s', $this->_distinct ? 'DISTINCT' : '', implode(', ', $this->_queryColumns), $this->table);

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

        if($this->_optionStr !== NULL)
        {
            $query = sprintf('%s %s', $query, $this->_optionStr);
        }

        if(!empty($this->_orderRules))
        {
            $orderItems = array_map(function($rule) {
                return sprintf('%s %s', $rule['sort_key'], $rule['sort_direction']);
            }, $this->_orderRules);

            $query = sprintf('%s ORDER BY %s', $query, implode(', ', $orderItems));
        }

        if($this->_page >= 0 && $this->_feedsPerPage > 0)
        {
            $query = sprintf('%s LIMIT %d, %d', $query, $this->_page * $this->_feedsPerPage, $this->_feedsPerPage);
        }

        return $this->database->query($query, $params);
    }

    public function querySingle()
    {
        $items = $this->queryList();
        if(count($items) > 0)
        {
            return $items[0];
        }

        return NULL;
    }

    public function queryCount()
    {
        $this->checkTableValid();

        if($this->_distinct)
        {
            $query = sprintf('SELECT COUNT(DISTINCT %s) AS count FROM %s', implode(', ', $this->_queryColumns), $this->table);
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

        $result = $this->database->query($query, $this->stmtValues());
        return $result[0]['count'];
    }
}