<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core;
use HQGames\addons\AddonManager;
use HQGames\addons\commando\Commando;
use HQGames\Core\commands\TestCommand;
use HQGames\addons\fakeblocks\FakeBlockManager;
use HQGames\addons\simplepackethandler\SimplePacketHandler;
use HQGames\Permissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use ReflectionClass;


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
		include_once __DIR__ . "/functions.php";
	}

	/**
	 * Function onEnable
	 * @return void
	 */
	public function onEnable(): void{
		$this->registerAddons();
		$this->registerCommands();
		$this->registerListeners();

		$this->getLogger()->info("Core is enabled!");
	}

	/**
	 * Function onDisable
	 * @return void
	 */
	public function onDisable(): void{
		AddonManager::getInstance()->unregisterAll();
		$this->getLogger()->info("Core is disabled!");
	}

	/**
	 * Function registerCommands
	 * @return void
	 */

	private function registerCommands(): void{
		Permissions::register();
		$commands = [
			new TestCommand,
		];
		foreach ($commands as $command) $this->getServer()->getCommandMap()->register(mb_strtolower($this->getDescription()->getName()), $command);
	}

	/**
	 * Function registerListeners
	 * @return void
	 */
	private function registerListeners(): void{
	}

	/**
	 * Function registerListeners
	 * @return void
	 */
	private function registerAddons(): void{
		new AddonManager($this);
		$this->getLogger()->info("Registering addons...");
		$addons = [
			SimplePacketHandler::class,
			Commando::class,
			FakeBlockManager::class,
		];
		foreach ($addons as $addon) {
			if (!class_exists($addon)) {
				$this->getLogger()->error("Addon '{$addon}' is not found!");
				continue;
			}
			AddonManager::getInstance()->registerAddon($addon);
			$this->getLogger()->debug("Addon '{$addon}' is registered!");
		}
	}
}
