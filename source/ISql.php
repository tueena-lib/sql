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

use tueenaLib\sql\results\IDeleteResult;
use tueenaLib\sql\results\IInsertResult;
use tueenaLib\sql\results\ISelectResult;
use tueenaLib\sql\results\IUpdateResult;

interface ISql
{
	public function commit(Transaction $transaction);
	public function rollBack(Transaction $transaction);

	public function select(string $query, array $parameters = [], Transaction $transaction = null): ISelectResult;
	public function insert(string $query, array $parameters = [], Transaction $transaction = null): IInsertResult;
	public function update(string $query, array $parameters = [], Transaction $transaction = null): IUpdateResult;
	public function delete(string $query, array $parameters = [], Transaction $transaction = null): IDeleteResult;

	public function selectWithPreparedStatement(PreparedStatement $preparedStatement, array $parameters = [], Transaction $transaction = null): ISelectResult;
	public function insertWithPreparedStatement(PreparedStatement $preparedStatement, array $parameters = [], Transaction $transaction = null): IInsertResult;
	public function updateWithPreparedStatement(PreparedStatement $preparedStatement, array $parameters = [], Transaction $transaction = null): IUpdateResult;
	public function deleteWithPreparedStatement(PreparedStatement $preparedStatement, array $parameters = [], Transaction $transaction = null): IDeleteResult;
}
