<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\Core\addons;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;


/**
 * Class AddonSingletonTrait
 * @package HQGames\Core\addons
 * @author Jan Sohn / xxAROX
 * @date 22. July, 2022 - 14:34
 * @ide PhpStorm
 * @project Core
 */
trait AddonSingletonTrait{
	private static ?self $instance = null;

	/**
	 * Function make
	 * @return Addon|AddonSingletonTrait
	 */
	#[Pure] private static function make(): self{
		return new self;
	}

	/**
	 * Function getInstance
	 * @return static
	 */
	public static function getInstance(): self{
		if (self::$instance === null) {
			self::$instance = self::make();
		}
		return self::$instance;
	}

	/**
	 * Function setInstance
	 * @param AddonSingletonTrait $instance
	 * @return void
	 */
	protected static function setInstance(self $instance): void{
		self::$instance = $instance;
	}

	/**
	 * Function destroy
	 * @return void
	 */
	public static function destroy(): void{
		self::$instance = null;
	}
}
