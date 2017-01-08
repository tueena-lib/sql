tueena-lib/sql
==============
A (very restricted) wrapper around PDO to make database access mockable in unit tests.

Features
--------
* Methods for `SELECT`, `INSERT`, `UPDATE` and `DELETE` queries.
* Prepared statements, transactions.
* Allows to pass parameters as associative array.
* Right now only works with MySQL.

Usage
-----
```php
<?php

namespace tueenaLib\sql;

// Initialisation
$driver = new MySqlDriver('localhost', 'root', 'myPasswd', 'myDatabase', 3306);
$sql = new Sql($driver);

// Usage

// INSERT with prepared statement (works also with UPDATEs, SELECTs and DELETEs).
$preparedStatement = new PreparedStatement('INSERT into testtable (foo, bar) VALUES (:foo, :bar)');
$sql->insertWithPreparedStatement($preparedStatement, ['foo' => 'value1', 'bar' => 'value2']);

$result = $sql->insertWithPreparedStatement($preparedStatement, ['foo' => 'value2', 'bar' => 'value3']);
$affectedRows = $result->getNumAffectedRows();
$lastInsertId = $result->getLastInsertId(); // Works only for INSERT queries, of course.

// Transactions
$transaction = $sql->beginTransaction();
// UPDATE without prepared statement (like SELECTs, INSERTs and DELETEs)
$sql->update(
	'UPDATE testtable SET foo = :foo WHERE id = :id',
	[
		'foo' => 'value4',
		'id' => 1
	],
	$transaction // pass in the transaction object.
);
$sql->rollBack($transaction); // or use Sql::commit($transaction)

$result = $sql->select('SELECT * FROM testtable');
$numRows = $result->getNumRows();
$row1 = $result->fetchAssoc(); // associative array
$row2 = $result->fetchNumeric(); // numeric array
$row3 = $result->fetchNumeric(); // returns null if there is no more row.
```

License
-------
MIT

Requirements
------------
php >= 7.0.0
pdo

Installation
------------
If you use `Composer`:
```
composer require tueena-lib/sql
```
Otherwise just download the files and use it.

Contact
-------
Bastian Fenske <bastian.fenske@tueena.org>
