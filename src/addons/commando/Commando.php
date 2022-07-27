<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\addons\commando;
use HQGames\addons\Addon;
use HQGames\addons\AddonManager;
use HQGames\addons\AddonSingletonTrait;
use HQGames\addons\simplepackethandler\SimplePacketHandler;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\Server;


/**
 * Class Commando
 * @package HQGames\addons\commando
 * @author Jan Sohn / xxAROX
 * @date 22. July, 2022 - 04:59
 * @ide PhpStorm
 * @project Core
 */
class Commando extends Addon implements Listener{
	use AddonSingletonTrait;


	private static bool $isIntercepting = false;

	public function onEnable(): void{
		if (!AddonManager::isRegistered(SimplePacketHandler::class)) AddonManager::getInstance()->register(SimplePacketHandler::class);
		$interceptor = SimplePacketHandler::getInstance()->createInterceptor(EventPriority::NORMAL, false);
		$interceptor->interceptOutgoing(function (AvailableCommandsPacket $pk, NetworkSession $target): bool{
			if (self::$isIntercepting) return true;
			$p = $target->getPlayer();
			foreach ($pk->commandData as $commandName => $commandData) {
				$cmd = Server::getInstance()->getCommandMap()->getCommand($commandName);
				if ($cmd instanceof CommandoCommand) {
					if (!$cmd->testForPlayerSilent($p)) continue;
					$pk->commandData[$commandName] = $cmd->getCommandData();
				}
			}
			$pk->softEnums = SoftEnumCache::getEnums();
			self::$isIntercepting = true;
			$target->sendDataPacket($pk);
			self::$isIntercepting = false;
			return false;
		});
		$this->registerListener($this);
	}

	public static function getVersion(): string{
		return "3.1.0";
	}

	public static function getAuthors(): array{
		return [ "xxAROX", "CortexPE" ];
	}
}
