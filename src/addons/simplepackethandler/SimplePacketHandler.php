<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\addons\simplepackethandler;
use HQGames\addons\Addon;
use HQGames\addons\AddonSingletonTrait;
use HQGames\addons\simplepackethandler\interceptor\IPacketInterceptor;
use HQGames\addons\simplepackethandler\interceptor\PacketInterceptor;
use HQGames\addons\simplepackethandler\monitor\IPacketMonitor;
use HQGames\addons\simplepackethandler\monitor\PacketMonitor;
use InvalidArgumentException;
use pocketmine\event\EventPriority;


/**
 * Class SimplePacketHandler
 * @package HQGames\addons\simplepackethandler
 * @author Muqsit Rayyan
 * @date 05. July, 2022 - 00:29
 * @ide PhpStorm
 * @project Core
 */
final class SimplePacketHandler extends Addon{
	use AddonSingletonTrait;
	/** @var PacketInterceptor[] */
	protected array $interceptors = [];
	/** @var PacketMonitor[] */
	protected array $monitors = [];


	public static function getVersion(): string{
		return "0.1.0";
	}

	public static function getAuthors(): array{
		return [
			"Muqsit",
			"xxAROX",
		];
	}

	public static function getLink(): string{
		return "https://raw.githubusercontent.com/Muqsit/SimplePacketHandler/master/virion.yml";
	}

	public function createInterceptor(int $priority = EventPriority::NORMAL, bool $handleCancelled = false): IPacketInterceptor{
		if ($priority === EventPriority::MONITOR) throw new InvalidArgumentException("Cannot intercept packets at MONITOR priority");
		$interceptor = new PacketInterceptor(self::getInstance()->getRegistrant(), $priority, $handleCancelled);
		$this->interceptors[spl_object_hash($interceptor)] = $interceptor;
		return $interceptor;
	}

	public function createMonitor(bool $handleCancelled = false): IPacketMonitor{
		$monitor = new PacketMonitor(self::getInstance()->getRegistrant(), $handleCancelled);
		$this->monitors[spl_object_hash($monitor)] = $monitor;
		return $monitor;
	}

	public function onDisable(): void{
		foreach ($this->interceptors as $interceptor) $interceptor->unregisterAll();
		foreach ($this->monitors as $monitor) $monitor->unregisterAll();
	}
}
