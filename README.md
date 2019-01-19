# 简介
这是一个对 MySQL 进行简单增删改查的调用框架，PHP 版。

### 其他版本
* [ObjC 版](https://github.com/fangqk1991/iOS-SQL)
* [Python 版](https://github.com/fangqk1991/py-sql)

### 依赖
* PHP 5.6+
* [Composer](https://getcomposer.org)

### 安装
编辑 `composer.json`，将 `fang/php-sql` 加入其中

```
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/fangqk1991/php-sql"
    }
  ],
  ...
  ...
  "require": {
    "fang/php-sql": "dev-master"
  }
}

```

执行命令

```
composer install
```

### 使用
#### FCDatabase
```
// 初始化
public function init($host, $account, $password, $dbName);

// 直接查询
public function query($query, $params);
```

#### BuilderBase (Adder/Modifier/Remover/Searcher 的基类)
```
// 初始化方法
public function __construct(FCDatabase $db);

// 添加执行条件（简单匹配）
public function addConditionKV($key, $value);

// 添加执行条件（自定义）
public function addSpecialCondition($condition, ...$args);
```

### SQLAdder
```
public function insertKV($key, $value);
public function execute();
```

### SQLModifier
```
public function updateKV($key, $value);
public function execute();
```

### SQLRemover
```
public function execute();
```

### SQLSearcher
```
// 采用 DISTINCT
public function markDistinct();

// 设置列
public function setColumns(array $columns);

// 添加列
public function addColumn($column)

// 添加排序规则
public function addOrderRule($sortKey, $direction)

// 设置页码信息
public function setPageInfo($page, $feedsPerPage);

// 设置附加语句
public function setOptionStr($optionStr)

// 查询
public function queryList();
public function queryCount();
```

### 示例
[Demo](https://github.com/fangqk1991/php-sql/tree/master/demos/sql-demo.php)


```
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
```

![](https://image.fangqk.com/2019-01-19/php-sql-demo.jpg)
