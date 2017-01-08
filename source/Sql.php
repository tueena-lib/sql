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
namespace tueenaLib\sql;

use tueenaLib\sql\drivers\IDriver;
use tueenaLib\sql\results\DeleteResult;
use tueenaLib\sql\results\IDeleteResult;
use tueenaLib\sql\results\IInsertResult;
use tueenaLib\sql\results\InsertResult;
use tueenaLib\sql\results\ISelectResult;
use tueenaLib\sql\results\IUpdateResult;
use tueenaLib\sql\results\SelectResult;
use tueenaLib\sql\results\UpdateResult;

class Sql implements ISql
{
	/**
	 * @var Transaction
	 */
	private $currentTransaction;

	/**
	 * Stores all prepared statements (query -> \PDOStatement)
	 *
	 * @var array
	 */
	private $preparedStatements = [];
	/**
	 * @var IDriver
	 */
	private $driver;

	/**
	 * @var \Pdo
	 */
	private $pdo;

	public function __construct(IDriver $driver)
	{
		$this->driver = $driver;
	}

	public function beginTransaction()
	{
		if (!is_null($this->currentTransaction))
			throw new \Exception('Commit or roll back the previous transaction before you start a new one.');
		$this->getPdo()->beginTransaction();
		return $this->currentTransaction = new Transaction;
	}

	public function commit(Transaction $transaction)
	{
		if ($this->currentTransaction !== $transaction)
			throw new \Exception('Cannot commit a transaction, if no transaction has been started.');
		$this->getPdo()->commit();
		$this->currentTransaction = null;
	}

	public function rollBack(Transaction $transaction)
	{
		if ($this->currentTransaction !== $transaction)
			throw new \Exception('Cannot roll back a transaction, if no transaction has been started.');
		$this->getPdo()->rollback();
		$this->currentTransaction = null;
	}

	public function select(string $query, array $parameters = [], Transaction $transaction = null): ISelectResult
	{
		$this->validateQueryType($query, 'SELECT');
		$this->validateTransaction($transaction);
		$result = $this->executeQuery($query, $parameters);
		return new SelectResult($result);
	}

	public function insert(string $query, array $parameters = [], Transaction $transaction = null): IInsertResult
	{
		$this->validateQueryType($query, 'INSERT');
		$this->validateTransaction($transaction);
		$result = $this->executeQuery($query, $parameters);
		return new InsertResult($result->rowCount(), (int) $this->getPdo()->lastInsertId());
	}

	public function update(string $query, array $parameters = [], Transaction $transaction = null): IUpdateResult
	{
		$this->validateQueryType($query, 'UPDATE');
		$this->validateTransaction($transaction);
		$result = $this->executeQuery($query, $parameters);
		return new UpdateResult($result->rowCount());
	}

	public function delete(string $query, array $parameters = [], Transaction $transaction = null): IDeleteResult
	{
		$this->validateQueryType($query, 'DELETE');
		$this->validateTransaction($transaction);
		$result = $this->executeQuery($query, $parameters);
		return new DeleteResult($result->rowCount());
	}

	public function selectWithPreparedStatement(PreparedStatement $preparedStatement, array $parameters = [], Transaction $transaction = null): ISelectResult
	{
		$this->validateQueryType($preparedStatement->getQuery(), 'SELECT');
		$this->validateTransaction($transaction);
		return new SelectResult($this->executePreparedStatement($preparedStatement, $parameters));
	}

	public function insertWithPreparedStatement(PreparedStatement $preparedStatement, array $parameters = [], Transaction $transaction = null): IInsertResult
	{
		$this->validateQueryType($preparedStatement->getQuery(), 'INSERT');
		$this->validateTransaction($transaction);
		$result = $this->executePreparedStatement($preparedStatement, $parameters);
		return new InsertResult($result->rowCount(), (int) $this->getPdo()->lastInsertId());
	}

	public function updateWithPreparedStatement(PreparedStatement $preparedStatement, array $parameters = [], Transaction $transaction = null): IUpdateResult
	{
		$this->validateQueryType($preparedStatement->getQuery(), 'UPDATE');
		$this->validateTransaction($transaction);
		$result = $this->executePreparedStatement($preparedStatement, $parameters);
		return new UpdateResult($result->rowCount());
	}

	public function deleteWithPreparedStatement(PreparedStatement $preparedStatement, array $parameters = [], Transaction $transaction = null): IDeleteResult
	{
		$this->validateQueryType($preparedStatement->getQuery(), 'DELETE');
		$this->validateTransaction($transaction);
		$result = $this->executePreparedStatement($preparedStatement, $parameters);
		return new DeleteResult($result->rowCount());
	}

	private function getPdo()
	{
		if (is_null($this->pdo))
			$this->pdo = $this->driver->connect();
		return $this->pdo;
	}

	private function executeQuery(string $query, array $parameters)
	{
		return $this->getPdo()->query($this->buildQuery($query, $parameters));
	}

	private function executePreparedStatement(PreparedStatement $preparedStatement, array $parameters): \PDOStatement
	{
		$query = $preparedStatement->getQuery();
		if (!array_key_exists($query, $this->preparedStatements))
			$this->preparedStatements[$query] = $this->getPdo()->prepare($query);

		$preparedStatement = $this->preparedStatements[$query];
		$preparedStatementParameters = self::createQueryParametersArray($parameters);
		$preparedStatement->execute($preparedStatementParameters);
		return $preparedStatement;
	}

	private function buildQuery(string $query, array $parameters): string
	{
		$map = [];
		foreach ($parameters as $key => $value)
			$map[':' . $key] = $this->quote($value);
		return strtr($query, $map);
	}

	private static function createQueryParametersArray(array $parameters): array
	{
		$map = [];
		foreach ($parameters as $key => $value)
			$map[':' . $key] = $value;
		return $map;
	}

	private function quote($value): string
	{
		if (is_null($value))
			return 'null';
		return $this->getPdo()
			->quote((string)$value);
	}

	private function validateQueryType(string $query, string $expectedType)
	{
		if (stripos($query, $expectedType . ' ') !== 0)
			throw new \Exception('The passed in query is not a ' . $expectedType . ' query.');
	}

	private function validateTransaction(Transaction $transaction = null)
	{
		if (!is_null($this->currentTransaction)) {
			if (is_null($transaction))
				throw new \Exception('Pass in the transaction object if you are executing database actions within an transaction.');
			elseif ($this->currentTransaction !== $transaction)
				throw new \Exception('You have to commit or roll back a transaction before you start another one.');
		} else {
			if (!is_null($transaction))
				throw new \Exception('Passed in an transaction object without having started a transaction. Call beginTransaction() first.');
		}
	}
}
