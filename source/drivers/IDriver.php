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
namespace tueenaLib\sql\drivers;

use tueenaLib\sql\results\IDeleteResult;
use tueenaLib\sql\results\IInsertResult;
use tueenaLib\sql\results\ISelectResult;
use tueenaLib\sql\results\IUpdateResult;
use tueenaLib\sql\transactions\ITransaction;

interface IDriver
{
	public function connect(): \PDO;
}
