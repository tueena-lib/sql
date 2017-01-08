<?php

/**
 * Part of the tueena lib
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @link http://tueena.org/
 * @author Bastian Fenske <bastian.fenske@tueena.org>
 * @file
 */

declare(strict_types=1);
namespace tueenaLib\loader\spec;

use tueenaLib\sql\drivers\MySqlDriver;
use tueenaLib\sql\PreparedStatement;
use tueenaLib\sql\Sql;
use tueenaLib\sql\Transaction;

class SqlTest extends \PHPUnit_Framework_TestCase
{
	private static function getConnectionData()
	{
		return [TEST_DATABASE_HOST_NAME, TEST_DATABASE_PORT, TEST_DATABASE_USER_NAME, TEST_DATABASE_PASSWORD, TEST_DATABASE_DATABASE_NAME];
	}

	private static function getConnection()
	{
		static $mySqlI = null;
		if (!is_null($mySqlI))
			return $mySqlI;
		list($hostName, $port, $userName, $password, $databaseName) = self::getConnectionData();
		$mySqlI = new \mysqli($hostName, $userName, $password, $databaseName, $port);
		$mySqlI->set_charset('utf8');
		mysqli_report(MYSQLI_REPORT_ALL);
		return $mySqlI;
	}

	public function setUp()
	{
		$this->getConnection()->query('DROP TABLE IF EXISTS `testtable`');
		$this->getConnection()->query('CREATE TABLE `testtable` ( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `foo` VARCHAR(80) NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;');
	}

	public function tearDown()
	{
		$this->getConnection()->query('DROP TABLE `testtable`');
	}

	/**
	 * @test
	 */
	public function The_class_can_execute_crud_operations()
	{
		list($hostName, $port, $userName, $password, $databaseName) = self::getConnectionData();
		$driver = new MySqlDriver($hostName, $userName, $password, $databaseName, $port);
		$sql = new Sql($driver);
		$result = $sql->insert(
			'INSERT INTO testtable (foo) VALUES (:foo)',
			['foo' => null]
		);
		$this->assertEquals(1, $result->getNumAffectedRows());
		$this->assertEquals(1, $result->getLastInsertId());

		$result = $sql->insert(
			'INSERT INTO testtable (foo) VALUES (:foo), (:foo), (:foo)',
			['foo' => 'test']
		);
		$this->assertEquals(3, $result->getNumAffectedRows());
		// The MySQL function LAST_INSER_ID() returns the ID of the first inserted row.
		$this->assertEquals(2, $result->getLastInsertId());

		$result = $sql->update(
			'UPDATE testtable SET foo = :foo WHERE id > :id',
			['id' => 2, 'foo' => 'anotherTestValue']
		);
		$this->assertEquals(2, $result->getNumAffectedRows());

		$result = $sql->delete(
			'DELETE FROM testtable WHERE id = :id',
			['id' => 3]
		);
		$this->assertEquals(1, $result->getNumAffectedRows());

		$result = $sql->select('SELECT * FROM testtable');
		$this->assertEquals(['1', null], $result->fetchNumeric());
		$this->assertEquals(['2', 'test'], $result->fetchNumeric());
		$this->assertEquals(['id' => '4', 'foo' => 'anotherTestValue'], $result->fetchAssoc());
		$this->assertNull($result->fetchAssoc());
		$this->assertEquals(3, $result->getNumRows());
	}

	/**
	 * @test
	 */
	public function The_class_can_execute_prepared_statements()
	{
		list($hostName, $port, $userName, $password, $databaseName) = self::getConnectionData();
		$driver = new MySqlDriver($hostName, $userName, $password, $databaseName, $port);
		$sql = new Sql($driver);

		$preparedInsertStatement = new PreparedStatement('INSERT INTO testtable (foo) VALUES (:foo)');

		$result = $sql->insertWithPreparedStatement($preparedInsertStatement, ['foo' => null]);
		$this->assertEquals(1, $result->getNumAffectedRows());
		$this->assertEquals(1, $result->getLastInsertId());

		$sql->insertWithPreparedStatement($preparedInsertStatement, ['foo' => 'test']);
		$sql->insertWithPreparedStatement($preparedInsertStatement, ['foo' => 'test']);
		$result = $sql->insertWithPreparedStatement($preparedInsertStatement, ['foo' => 'test']);
		$this->assertEquals(4, $result->getLastInsertId());

		$preparedUpdateStatement = new PreparedStatement('UPDATE testtable SET foo = :foo WHERE id > :id');
		$result = $sql->updateWithPreparedStatement($preparedUpdateStatement, ['id' => 2, 'foo' => 'anotherTestValue']);
		$this->assertEquals(2, $result->getNumAffectedRows());

		$preparedDeleteStatement = new PreparedStatement('DELETE FROM testtable WHERE id = :id');
		$result = $sql->deleteWithPreparedStatement($preparedDeleteStatement, ['id' => 3]);
		$this->assertEquals(1, $result->getNumAffectedRows());

		$preparedSelectStatement = new PreparedStatement('SELECT * FROM testtable WHERE id > :id');
		$result = $sql->selectWithPreparedStatement($preparedSelectStatement, ['id' => 0]);
		$this->assertEquals(['1', null], $result->fetchNumeric());
		$this->assertEquals(['2', 'test'], $result->fetchNumeric());
		$this->assertEquals(['id' => '4', 'foo' => 'anotherTestValue'], $result->fetchAssoc());
		$this->assertNull($result->fetchAssoc());
		$this->assertEquals(3, $result->getNumRows());
	}

	/**
	 * @test
	 */
	public function Transactions_are_supported()
	{
		list($hostName, $port, $userName, $password, $databaseName) = self::getConnectionData();
		$driver = new MySqlDriver($hostName, $userName, $password, $databaseName, $port);
		$sql = new Sql($driver);

		$sql->insert('INSERT into testtable (foo) VALUES (:foo)', ['foo' => 'myFirstValue']);
		$transaction1 = $sql->beginTransaction();
		$sql->insert('INSERT into testtable (foo) VALUES (:foo)', ['foo' => 'mySecondValue'], $transaction1);
		$sql->update('UPDATE testtable SET foo = :foo WHERE id = :id', ['foo' => 'myUpdatedFirstValue', 'id' => 1], $transaction1);
		$resultWithinTransaction = $sql->select('SELECT * FROM testtable', [], $transaction1);
		$this->assertEquals(2, $resultWithinTransaction->getNumRows());
		$this->assertEquals(['1', 'myUpdatedFirstValue'], $resultWithinTransaction->fetchNumeric());
		$this->assertEquals(['2', 'mySecondValue'], $resultWithinTransaction->fetchNumeric());
		$sql->rollBack($transaction1);
		$resultAfterRollback = $sql->select('SELECT * FROM testtable');
		$this->assertEquals(1, $resultAfterRollback->getNumRows());
		$this->assertEquals(['1', 'myFirstValue'], $resultAfterRollback->fetchNumeric());

		$transaction2 = $sql->beginTransaction();
		$sql->insert('INSERT into testtable (foo) VALUES (:foo)', ['foo' => 'mySecondValue'], $transaction2);
		$sql->update('UPDATE testtable SET foo = :foo WHERE id = :id', ['foo' => 'myUpdatedFirstValue', 'id' => 1], $transaction2);
		$sql->commit($transaction2);
		$resultAfterCommit = $sql->select('SELECT * FROM testtable');
		$this->assertEquals(2, $resultAfterCommit->getNumRows());
		$this->assertEquals(['1', 'myUpdatedFirstValue'], $resultAfterCommit->fetchNumeric());
		$this->assertEquals(['3', 'mySecondValue'], $resultAfterCommit->fetchNumeric());
	}
}
