<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.local/MyConfig.php';

use FC\SQL\MysqlDB;
use FC\SQL\SQLSearcher;

$db = MysqlDB::getInstance();
$db->init(MyConfig::SQL_Host, MyConfig::SQL_Account, MyConfig::SQL_Password, MyConfig::SQL_DBName);

$builder = new SQLSearcher($db);
$builder->setTable('manager');
$builder->addColumn('*');
$builder->setPageInfo(0, 3);
$items = $builder->queryList();

var_dump($items);