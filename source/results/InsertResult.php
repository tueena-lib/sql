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

class InsertResult implements IInsertResult
{
	/**
	 * @var int
	 */
	private $numAffectedRows;

	/**
	 * @var int
	 */
	private $lastInsertId;

	public function __construct(int $numAffectedRows, int $lastInsertId)
	{
		$this->numAffectedRows = $numAffectedRows;
		$this->lastInsertId = $lastInsertId;
	}

	public function getNumAffectedRows()
	{
		return $this->numAffectedRows;
	}

	public function getLastInsertId()
	{
		return $this->lastInsertId;
	}
}
