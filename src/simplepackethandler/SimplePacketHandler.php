<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core\simplepackethandler;
use HQGames\Core\Core;
use HQGames\Core\simplepackethandler\interceptor\IPacketInterceptor;
use HQGames\Core\simplepackethandler\interceptor\PacketInterceptor;
use InvalidArgumentException;
use pocketmine\event\EventPriority;


/**
 * Class SimplePacketHandler
 * @package HQGames\Core\simplepackethandler
 * @author Muqsit Rayyan
 * @date 05. July, 2022 - 00:29
 * @ide PhpStorm
 * @project Core
 */
final class SimplePacketHandler{
	public static function createInterceptor(Core $registerer, int $priority = EventPriority::NORMAL, bool $handleCancelled = false) : IPacketInterceptor{
		if ($priority === EventPriority::MONITOR) throw new InvalidArgumentException("Cannot intercept packets at MONITOR priority");
		return new PacketInterceptor($registerer, $priority, $handleCancelled);
	}

	public static function createMonitor(Core $registerer, bool $handleCancelled = false) : IPacketMonitor{
		return new PacketMonitor($registerer, $handleCancelled);
	}
}
