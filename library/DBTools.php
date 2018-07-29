<?php

namespace FC\SQL;

class DBTools
{
    private $_handler;

    public function __construct(ISQLHandler $handler)
    {
        $this->_handler = $handler;
    }

    public function add($params)
    {
        $handler = $this->_handler;
        $sql = $handler->sql_instance();
        $table = $handler->sql_table();
        $cols = $handler->sql_insertableCols();

        $builder = new SQLAdder($sql);
        $builder->setTable($table);

        foreach($cols as $key)
        {
            $value = NULL;
            if(isset($params[$key]))
            {
                $value = $params[$key];
            }
            $builder->insertKV($key, $value);
        }

        return $builder->execute();
    }

    public function update($params)
    {
        $handler = $this->_handler;
        $sql = $handler->sql_instance();
        $table = $handler->sql_table();
        $cols = $handler->sql_modifiableCols();

        $builder = new SQLModifier($sql);
        $builder->setTable($table);

        $pKey = $handler->sql_primaryKey();
        $pKeys = is_array($pKey) ? $pKey : array($pKey);
        foreach ($pKeys as $key)
        {
            $builder->checkPrimaryKey($params, $key);
            unset($params[$key]);
        }

        foreach($cols as $key)
        {
            if(array_key_exists($key, $params))
            {
                $builder->updateKV($key, $params[$key]);
            }
        }

        $builder->execute();
    }

    public function delete($params)
    {
        $handler = $this->_handler;
        $sql = $handler->sql_instance();
        $table = $handler->sql_table();

        $builder = new SQLRemover($sql);
        $builder->setTable($table);

        $pKey = $handler->sql_primaryKey();
        $pKeys = is_array($pKey) ? $pKey : array($pKey);
        foreach ($pKeys as $key)
        {
            $builder->checkPrimaryKey($params, $key);
        }

        $builder->execute();
    }

    public function searchSingle($params, $checkPrimaryKey = TRUE)
    {
        if($checkPrimaryKey)
        {
            $handler = $this->_handler;
            $pKey = $handler->sql_primaryKey();
            $pKeys = is_array($pKey) ? $pKey : array($pKey);
            foreach ($pKeys as $key)
            {
                if(!array_key_exists($key, $params))
                {
                    throw new SQLException(sprintf('%s: primary key missing.', get_class()));
                }
            }
        }

        $items = $this->fetchList($params, 0, 1);
        if(count($items) > 0)
        {
            return $items[0];
        }

        return NULL;
    }

    public function fetchList($params, $page, $itemsPerPage)
    {
        $handler = $this->_handler;
        $sql = $handler->sql_instance();
        $table = $handler->sql_table();
        $cols = $handler->sql_cols();

        $builder = new SQLSearcher($sql);
        $builder->setTable($table);
        $builder->setPageInfo($page, $itemsPerPage);

        foreach($cols as $key)
        {
            $builder->addColumn($key);
        }

        foreach ($params as $key => $value)
        {
            $builder->addConditionKV($key, $value);
        }

        return $builder->queryList();
    }

    public function fetchCount($params)
    {
        $handler = $this->_handler;
        $sql = $handler->sql_instance();
        $table = $handler->sql_table();
        $cols = $handler->sql_cols();

        $builder = new SQLSearcher($sql);
        $builder->setTable($table);

        foreach($cols as $key)
        {
            $builder->addColumn($key);
        }

        foreach ($params as $key => $value)
        {
            $builder->addConditionKV($key, $value);
        }

        return $builder->queryCount();
    }
}