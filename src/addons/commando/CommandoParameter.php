<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\addons\commando;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;


/**
 * Class CommandoParameter
 * @package HQGames\addons\commando
 * @author Jan Sohn / xxAROX
 * @date 24. July, 2022 - 14:31
 * @ide PhpStorm
 * @project Core
 */
class CommandoParameter extends CommandParameter{
	public const FLAG_FORCE_COLLAPSE_ENUM = 0x1;
	public const FLAG_HAS_ENUM_CONSTRAINT = 0x2;

	public string $paramName;
	public int $paramType;
	public ?string $permission = null;
	public bool $isOptional;
	public int $flags = 0; //shows enum name if 1, always zero except for in /gamerule command
	public ?CommandEnum $enum = null;
	public ?string $postfix = null;

	/**
	 * Function baseline
	 * @param string $name
	 * @param null|string $permission
	 * @param int $type
	 * @param int $flags
	 * @param bool $optional
	 * @return static
	 */
	private static function baseline(string $name, ?string $permission, int $type, int $flags, bool $optional) : self{
		$result = new self;
		$result->paramName = $name;
		$result->permission = $permission;
		$result->paramType = $type;
		$result->flags = $flags;
		$result->isOptional = $optional;
		return $result;
	}

	/**
	 * Function standard
	 * @param string $name
	 * @param int $type
	 * @param int $flags
	 * @param bool $optional
	 * @param null|string $permission
	 * @return static
	 */
	public static function standard(string $name, int $type, int $flags = 0, bool $optional = false, ?string $permission = null) : self{
		return self::baseline($name, $permission, AvailableCommandsPacket::ARG_FLAG_VALID | $type, $flags, $optional);
	}

	/**
	 * Function postfixed
	 * @param string $name
	 * @param string $postfix
	 * @param int $flags
	 * @param bool $optional
	 * @param null|string $permission
	 * @return static
	 */
	public static function postfixed(string $name, string $postfix, int $flags = 0, bool $optional = false, ?string $permission = null) : self{
		$result = self::baseline($name, $permission, AvailableCommandsPacket::ARG_FLAG_POSTFIX, $flags, $optional);
		$result->postfix = $postfix;
		return $result;
	}

	/**
	 * Function enum
	 * @param string $name
	 * @param CommandEnum $enum
	 * @param int $flags
	 * @param bool $optional
	 * @param null|string $permission
	 * @return static
	 */
	public static function enum(string $name, CommandEnum $enum, int $flags, bool $optional = false, ?string $permission = null) : self{
		$result = self::baseline($name, $permission, AvailableCommandsPacket::ARG_FLAG_ENUM | AvailableCommandsPacket::ARG_FLAG_VALID, $flags, $optional);
		$result->enum = $enum;
		return $result;
	}
}
