<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\utils;
use Medoo\Medoo;


/**
 * Class MySQLDatabase
 * @package HQGames\utils
 * @author Jan Sohn / xxAROX
 * @date 23. July, 2022 - 20:04
 * @ide PhpStorm
 * @project Core
 */
class MySQLDatabase{
	protected Medoo $medoo;

	/**
	 * MySQLDatabase constructor.
	 * @param string $user
	 * @param string $password
	 * @param string $database
	 * @param string $host
	 * @param int $port
	 */
	public function __construct(string $user, string $password, string $database, string $host = "localhost", int $port = 3306){
		$this->medoo = new Medoo([
			"type"     => "mysql",
			"host"     => $host,
			"port"     => $port,
			"database" => $database,
			"username" => $user,
			"password" => $password,
		]);
	}

	/**
	 * Function getMedoo
	 * @return Medoo
	 */
	public function getMedoo(): Medoo{
		return $this->medoo;
	}
}
