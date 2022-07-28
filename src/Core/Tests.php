<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core;
use Closure;
use HQGames\forms\elements\Button;
use HQGames\forms\types\MenuForm;
use pocketmine\utils\SingletonTrait;
use ReflectionClass;


/**
 * Class Tests
 * @package HQGames\Core
 * @author Jan Sohn / xxAROX
 * @date 14. July, 2022 - 01:00
 * @ide PhpStorm
 * @project Core
 */
class Tests{
	use SingletonTrait{
		setInstance as private;
		reset as private;
	}

	private function __construct(){
	}

	/**
	 * Function getForm
	 * @return MenuForm
	 */
	function getForm(): MenuForm{
		$buttons = [];
		foreach ((new ReflectionClass(self::class))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			if ($method->getName() === "__construct") continue;
			if ($method->getName() === "getForm") continue;
			$buttons[] = new Button($method->getName(), Closure::fromCallable([$this, $method->getName()]));
		}
		return new MenuForm(
			"§dTests",
			"",
			$buttons,
		);
	}
}
