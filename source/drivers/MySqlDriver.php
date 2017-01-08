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

class MySqlDriver implements IDriver
{
	/**
	 * @var string
	 */
	private $hostName;
	/**
	 * @var string
	 */
	private $userName;
	/**
	 * @var string
	 */
	private $password;
	/**
	 * @var string
	 */
	private $databaseName;
	/**
	 * @var int
	 */
	private $port;

	public function __construct(string $hostName, string $userName, string $password, string $databaseName, int $port = 3306)
	{
		$this->hostName = $hostName;
		$this->userName = $userName;
		$this->password = $password;
		$this->databaseName = $databaseName;
		$this->port = $port;
	}

	public function connect(): \PDO
	{
		$options = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
		$dsn = self::createDsn($this->hostName, $this->databaseName, $this->port);
		return new \PDO($dsn, $this->userName, $this->password, $options);
	}

	private static function createDsn(string $hostName, string $databaseName, int $port): string
	{
		return "mysql:host=$hostName;port=$port;dbname=$databaseName;charset=utf8";
	}
}
