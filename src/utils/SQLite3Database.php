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
 * Class SQLite3Database
 * @package HQGames\utils
 * @author Jan Sohn / xxAROX
 * @date 23. July, 2022 - 20:03
 * @ide PhpStorm
 * @project Core
 */
class SQLite3Database{
	protected Medoo $medoo;

	/**
	 * SQLite3Database constructor.
	 * @param string $file
	 */
	public function __construct(string $file){
		$this->medoo = new Medoo([
			"database_type" => "sqlite",
			"database_file" => $file,
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
