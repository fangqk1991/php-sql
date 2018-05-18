<?php

namespace FC\SQL;

class MysqlDB
{
    private $_db = NULL;
    private $_host;
    private $_account;
    private $_password;
    private $_dbName;

    private function db()
    {
        if(!($this->_db instanceof \mysqli))
        {
            $this->_db = new \mysqli($this->_host, $this->_account, $this->_password, $this->_dbName);

            if($this->_db->errno)
            {
                throw new \Exception(sprintf('Connecting error. %s', $this->_db->errno), $this->_db->errno);
            }

            if (!$this->_db->set_charset("utf8mb4"))
            {
                throw new \Exception(sprintf('Error loading character set utf8: %s', $this->_db->errno), $this->_db->errno);
            }
        }

        return $this->_db;
    }

    public function checkQueryValid($query, $params)
    {
        $valid = false;

        $param_count1 = substr_count($query, '?');
        $param_count2 = count($params);

        if($param_count1 == 0 && $param_count2 == 0)
        {
            $valid = true;
        }
        else if($param_count1 > 0 && $param_count2 == $param_count1 + 1)
        {
            $param_head = $params[0];
            if(strlen($param_head) == $param_count1)
            {
                $valid = true;
            }
        }

        if(!$valid)
        {
            throw new SQLException($params);
        }
    }

    public function lastInsertID()
    {
        return $this->db()->insert_id;
    }

    public function query($query, $params)
    {
        $db = $this->db();

        $this->checkQueryValid($query, $params);

        $stmt = $db->prepare($query);

        if(count($params) >= 2)
        {
            $params_in = array();
            for($i = 0; $i < count($params); ++$i)
            {
                $params_in[] = &$params[$i];
            }
            @call_user_func_array(array($stmt, 'bind_param'), $params_in);
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
            return array();
        }

        $params_out = array();
        $row = array();
        while($field = $meta->fetch_field())
        {
            $params_out[] = &$row[$field->name];
        }
        @call_user_func_array(array($stmt, 'bind_result'), $params_out);

        $results = array();
        while($stmt->fetch())
        {
            $x = array();
            foreach($row as $key => $val )
            {
                $x[$key] = $val;
            }
            array_push($results, $x);
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
}
