<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\utils\SingletonTrait;
use ReflectionClass;
use ReflectionProperty;


/**
 * Class Permissions
 * @package HQGames
 * @author Jan Sohn / xxAROX
 * @date 22. July, 2022 - 22:01
 * @ide PhpStorm
 * @project Bridge
 */
class Permissions{
	const COMMAND_TEST = ["hqgames.command.test", "Allows to use the test command."];

	private static Permissions $instance;
	private static Permission $overlord;
	/** @var Permission[] */
	private static array $permissions = [];

	private function __construct(){
		PermissionManager::getInstance()->addPermission(Permissions::$overlord = new Permission("overlord", "Overlord permission"));
		$this->register();
	}

	/**
	 * Function getInstance
	 * @return Permissions
	 */
	public static function getInstance(): Permissions{
		return self::$instance ?? self::$instance = new Permissions();
	}

	/**
	 * Function registerPermissions
	 * @return void
	 */
	public static function register(): void{
		foreach ((new ReflectionClass(static::class))->getConstants(ReflectionProperty::IS_PUBLIC) as $constant)
			PermissionManager::getInstance()->addPermission(new Permission($constant[0], (!is_array($constant) || !isset($constant[1]) ? $constant[1] : "No description provided.")));
	}

	/**
	 * Function registerPermission
	 * @param Permission $permission
	 * @return void
	 */
	public static function registerPermission(Permission $permission): void{
		if (Permissions::isRegistered($permission->getName())) throw new InvalidArgumentException("Permission '{$permission->getName()}' is already registered");
		Permissions::$permissions[mb_strtolower($permission->getName())] = $permission;
		$opRoot = PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_OPERATOR);
		PermissionManager::getInstance()->addPermission($permission);
		Permissions::$overlord->addChild($permission->getName(), true);
		$opRoot->addChild($permission->getName(), true);
	}

	/**
	 * Function unregisterPermission
	 * @param Permission $permission
	 * @return void
	 */
	public static function unregisterPermission(Permission $permission): void{
		if (!Permissions::isRegistered($permission->getName())) throw new InvalidArgumentException("Permission '{$permission->getName()}' is not registered");
		unset(Permissions::$permissions[mb_strtolower($permission->getName())]);
		PermissionManager::getInstance()->removePermission($permission);
	}

	/**
	 * Function isRegistered
	 * @param Permission|string $name
	 * @return bool
	 */
	#[Pure] public static function isRegistered(Permission|string $name): bool{
		return isset(Permissions::$permissions[mb_strtolower(($name instanceof Permission ? $name->getName() : $name))]);
	}

	/**
	 * Function getPermission
	 * @param string $name
	 * @return null|Permission
	 */
	#[Pure] public static function getPermission(string $name): ?Permission{
		if (!Permissions::isRegistered($name)) return null;
		return Permissions::$permissions[mb_strtolower($name)] ?? null;
	}

	/**
	 * Function getPermissions
	 * @return Permission[]
	 */
	public static function getPermissions(): array{
		return Permissions::$permissions;
	}
}
