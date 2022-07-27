<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\addons\commando;
use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\UpdateSoftEnumPacket;
use pocketmine\Server;


/**
 * Class SoftEnumCache
 * @package HQGames\addons\commando
 * @author Jan Sohn / xxAROX
 * @date 24. July, 2022 - 14:36
 * @ide PhpStorm
 * @project Core
 */
class SoftEnumCache{
	/** @var CommandEnum[] */
	private static array $enums = [];

	/**
	 * Function getEnumByName
	 * @param string $name
	 * @return null|CommandEnum
	 */
	public static function getEnumByName(string $name): ?CommandEnum{
		return static::$enums[$name] ?? null;
	}

	/**
	 * Function getEnums
	 * @return CommandEnum[]
	 */
	public static function getEnums(): array{
		return static::$enums;
	}

	/**
	 * Function addEnum
	 * @param CommandEnum $enum
	 * @return void
	 */
	public static function addEnum(CommandEnum $enum): void{
		if (self::getEnumByName($enum->getName()) === null) throw new InvalidArgumentException("Enum with name " . $enum->getName() . " already exists");
		static::$enums[$enum->getName()] = $enum;
		self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_ADD);
	}

	/**
	 * Function updateEnum
	 * @param string $enumName
	 * @param array $values
	 * @return void
	 */
	public static function updateEnum(string $enumName, array $values): void{
		if (self::getEnumByName($enumName) === null) throw new CommandoException("Unknown enum named " . $enumName);
		$enum = self::$enums[$enumName] = new CommandEnum($enumName, $values);
		self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_SET);
	}

	/**
	 * Function removeEnum
	 * @param string $enumName
	 * @return void
	 */
	public static function removeEnum(string $enumName): void{
		if (($enum = self::getEnumByName($enumName)) === null) throw new CommandoException("Unknown enum named " . $enumName);
		unset(static::$enums[$enumName]);
		self::broadcastSoftEnum($enum, UpdateSoftEnumPacket::TYPE_REMOVE);
	}

	/**
	 * Function broadcastSoftEnum
	 * @param CommandEnum $enum
	 * @param int $type
	 * @return void
	 */
	private static function broadcastSoftEnum(CommandEnum $enum, int $type): void{
		$pk = new UpdateSoftEnumPacket();
		$pk->enumName = $enum->getName();
		$pk->values = $enum->getValues();
		$pk->type = $type;
		($sv = Server::getInstance())->broadcastPackets($sv->getOnlinePlayers(), [ $pk ]);
	}
}
