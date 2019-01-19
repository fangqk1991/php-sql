<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FC\SQL\FCDatabase;

define('HOST', '127.0.0.1');
define('ACCOUNT', 'SOME_ACCOUNT');
define('PASSWORD', 'SOME_PASSWORD');
define('DB_NAME', 'demo_db');
define('TABLE', 'demo_table');

$database = FCDatabase::getInstance();
$database->init(HOST, ACCOUNT, PASSWORD, DB_NAME);

function showRecords(FCDatabase $database)
{
    $searcher = $database->searcher();
    $searcher->setTable(TABLE);
    $searcher->setColumns(['uid', 'key1', 'key2']);
//    $searcher->setColumns(['*']);
    $items = $searcher->queryList();
    $count = $searcher->queryCount();
    echo sprintf("%d records: %s\n", $count, json_encode($items));
    return $items;
}

showRecords($database);

for($i = 0; $i < 5; ++$i)
{
    $adder = $database->adder();
    $adder->setTable(TABLE);
    $adder->insertKV('key1', sprintf('K1 - %04d', rand(0, 9999)));
    $adder->insertKV('key2', sprintf('K2 - %04d', rand(0, 9999)));
    $adder->execute();
}

showRecords($database);

$modifier = $database->modifier();
$modifier->setTable(TABLE);
$modifier->updateKV('key1', 'Odd');
$modifier->updateKV('key2', 'Changed');
$modifier->addConditionKV('MOD(uid, 2)', 1);
$modifier->execute();

showRecords($database);

$remover = $database->remover();
$remover->setTable(TABLE);
$remover->addSpecialCondition('uid > ?', 3);
$remover->execute();

showRecords($database);

