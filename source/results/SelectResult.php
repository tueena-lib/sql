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
namespace tueenaLib\sql\results;

class SelectResult implements ISelectResult
{
	/**
	 * @var \PDOStatement
	 */
	private $pdoStatement;

	public function __construct(\PDOStatement $pdoStatement)
	{
		$this->pdoStatement = $pdoStatement;
	}

	public function fetchNumeric()
	{
		$result = $this->pdoStatement->fetch(\PDO::FETCH_NUM);
		return $result ? $result : null;

	}

	public function fetchAssoc()
	{
		$result = $this->pdoStatement->fetch(\PDO::FETCH_ASSOC);
		return $result ? $result : null;
	}

	public function getNumRows(): int
	{
		return $this->pdoStatement->rowCount();
	}
}
