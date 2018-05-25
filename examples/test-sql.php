<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.local/MyConfig.php';

use FC\SQL\MysqlDB;

MysqlDB::getInstance()->init(MyConfig::SQL_Host, MyConfig::SQL_Account, MyConfig::SQL_Password, MyConfig::SQL_DBName);

//{
//    $builder = MysqlDB::getInstance()->searcher();
//    $builder->setTable('manager');
//    $builder->addColumn('*');
//    $builder->setPageInfo(0, 3);
//    $items = $builder->queryList();
//    var_dump($items);
//}

{
    $items = MysqlDB::getInstance()->query('SELECT * FROM manager WHERE account = ?', array('fang'));
    var_dump($items);
}