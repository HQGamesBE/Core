<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core;
use HQGames\Core\player\Player;
use pocketmine\world\Position;


/**
 * Class Options
 * @package HQGames\Core
 * @author Jan Sohn / xxAROX
 * @date 05. July, 2022 - 17:10
 * @ide PhpStorm
 * @project Core
 */
class Options{
	public static string $player_class = Player::class;
	public static bool $enable_corps = true;
	/** @var Position[] */
	public static array $start_jump_and_run_positions = [];

	static function load(): void{
		$config = Core::getInstance()->getConfig();
		self::$start_jump_and_run_positions = $config["start_jump_and_run_positions"];
	}
}
