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

class DeleteResult implements IDeleteResult
{
	/**
	 * @var int
	 */
	private $numAffectedRows;

	public function __construct(int $numAffectedRows)
	{
		$this->numAffectedRows = $numAffectedRows;
	}

	public function getNumAffectedRows()
	{
		return $this->numAffectedRows;
	}
}
