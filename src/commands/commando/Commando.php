<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\Core\commands\commando;
use HQGames\Core\addons\Addon;
use HQGames\Core\addons\AddonManager;
use HQGames\Core\addons\AddonSingletonTrait;
use HQGames\Core\simplepackethandler\SimplePacketHandler;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\UpdateSoftEnumPacket;
use pocketmine\Server;


/**
 * Class Commando
 * @package HQGames\Core\commands\commando
 * @author Jan Sohn / xxAROX
 * @date 22. July, 2022 - 04:59
 * @ide PhpStorm
 * @project Core
 */
class Commando extends Addon implements Listener{
	use AddonSingletonTrait;


	private static bool $isIntercepting = false;
	/** @var CommandEnum[] */
	private static array $enums = [];

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
			$pk->softEnums = self::getEnums();
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

	public static function getEnumByName(string $name): ?CommandEnum{
		return static::$enums[$name] ?? null;
	}

	/**
	 * @return CommandEnum[]
	 */
	public static function getEnums(): array{
		return static::$enums;
	}

	public static function addEnum(CommandEnum $enum): void{
		static::$enums[$enum->getName()] = $enum;
		self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_ADD);
	}

	public static function updateEnum(string $enumName, array $values): void{
		if (self::getEnumByName($enumName) === null) throw new CommandoException("Unknown enum named " . $enumName);
		$enum = self::$enums[$enumName] = new CommandEnum($enumName, $values);
		self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_SET);
	}

	public static function removeEnum(string $enumName): void{
		if (($enum = self::getEnumByName($enumName)) === null) throw new CommandoException("Unknown enum named " . $enumName);
		unset(static::$enums[$enumName]);
		self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_REMOVE);
	}

	public static function broadcastSoftEnum(CommandEnum $enum, int $type): void{
		$pk = new UpdateSoftEnumPacket();
		$pk->enumName = $enum->getName();
		$pk->values = $enum->getValues();
		$pk->type = $type;
		self::broadcastPacket($pk);
	}

	private static function broadcastPacket(ClientboundPacket $pk): void{
		($sv = Server::getInstance())->broadcastPackets($sv->getOnlinePlayers(), [ $pk ]);
	}
}
