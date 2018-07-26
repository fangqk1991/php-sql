<?php

namespace FC\SQL;

use mysqli;

class MysqlDB
{
    private $_db = NULL;
    private $_host;
    private $_account;
    private $_password;
    private $_dbName;

    private function db()
    {
        if(!($this->_db instanceof mysqli))
        {
            $this->_db = new mysqli($this->_host, $this->_account, $this->_password, $this->_dbName);

            if($this->_db->errno)
            {
                throw new SQLException(sprintf('Connecting error. %s', $this->_db->errno));
            }

            if (!$this->_db->set_charset("utf8mb4"))
            {
                throw new SQLException(sprintf('Error loading character set utf8: %s', $this->_db->errno));
            }
        }

        return $this->_db;
    }

    public function lastInsertID()
    {
        return $this->db()->insert_id;
    }

    public function query($query, $params)
    {
        if(substr_count($query, '?') !== count($params))
        {
            throw new SQLException('query params count not match');
        }

        $db = $this->db();
        $stmt = $db->prepare($query);

        if($stmt === FALSE)
        {
            throw new SQLException('Prepare query error!');
        }

        if(count($params) > 0)
        {
            $tmpl = sprintf('%%\'s%ds', count($params));
            $types = sprintf($tmpl, '');
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        if($stmt->errno === 1062)
        {
            throw new SQLException('Insert failure. ' . $stmt->error);
        }
        else if($stmt->errno !== 0)
        {
            throw new SQLException($stmt->error);
        }

        $meta = $stmt->result_metadata();
        if($meta == null)
        {
            $stmt->close();
            return [];
        }

        $paramsOut = [];
        $theRow = [];
        while($field = $meta->fetch_field())
        {
            $paramsOut[] = &$theRow[$field->name];
        }
        $stmt->bind_result(...$paramsOut);

        $results = [];
        while($stmt->fetch())
        {
            $x = [];
            foreach($theRow as $key => $value)
            {
                $x[$key] = $value;
            }
            $results[] = $x;
        }
        $stmt->close();

        while($db->more_results())
        {
            $db->next_result();
        }

        return $results;
    }

    private static $_instanceMap = array();
    public static function instanceWithName($name)
    {
        $obj = NULL;
        if(isset(self::$_instanceMap[$name]) && self::$_instanceMap[$name] instanceof self)
        {
            $obj = self::$_instanceMap[$name];
        }
        else
        {
            $obj = new self();
            self::$_instanceMap[$name] = $obj;
        }

        return $obj;
    }

    public static function getInstance()
    {
        return self::instanceWithName('default');
    }

    public function __clone()
    {
        die('Clone is not allowed.' . E_USER_ERROR);
    }

    public function init($host, $account, $password, $dbName)
    {
        $this->_host = $host;
        $this->_account = $account;
        $this->_password = $password;
        $this->_dbName = $dbName;

        return $this;
    }

    public function adder()
    {
        return new SQLAdder($this);
    }

    public function modifier()
    {
        return new SQLModifier($this);
    }

    public function remover()
    {
        return new SQLRemover($this);
    }

    public function searcher()
    {
        return new SQLSearcher($this);
    }
}
