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

interface ISelectResult
{
	public function fetchNumeric();
	public function fetchAssoc();
	public function getNumRows(): int;
}
