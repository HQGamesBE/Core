<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core;
use HQGames\Core\fakeblocks\FakeBlockManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;


/**
 * Class Core
 * @package HQGames\Core
 * @author Jan Sohn / xxAROX
 * @date 05. July, 2022 - 00:10
 * @ide PhpStorm
 * @project Core
 */
class Core extends PluginBase{
	use SingletonTrait;


	/**
	 * Function onLoad
	 * @return void
	 */
	public function onLoad(): void{
		$this->getLogger()->info("Core is loading...");
	}

	/**
	 * Function onEnable
	 * @return void
	 */
	public function onEnable(): void{
		FakeBlockManager::register($this);

		$this->getLogger()->info("Core is enabled!");
	}

	/**
	 * Function onDisable
	 * @return void
	 */
	public function onDisable(): void{
		$this->getLogger()->info("Core is disabled!");
	}
}
