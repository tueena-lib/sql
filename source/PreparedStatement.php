<?php


namespace tueenaLib\sql;

class PreparedStatement
{
	/**
	 * @var string
	 */
	private $query;

	public function __construct(string $query)
	{
		$this->query = $query;
	}

	/**
	 * @return string
	 */
	public function getQuery(): string
	{
		return $this->query;
	}
}
