<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\addons\simplepackethandler\monitor;
use Closure;
use HQGames\Core\Core;
use JetBrains\PhpStorm\Pure;
use pocketmine\plugin\PluginBase;


/**
 * Class PacketMonitor
 * @package HQGames\addons\simplepackethandler\monitor
 * @author Jan Sohn / xxAROX
 * @date 05. July, 2022 - 00:52
 * @ide PhpStorm
 * @project Core
 */
class PacketMonitor implements IPacketMonitor{
	private PacketMonitorListener $listener;

	#[Pure] public function __construct(PluginBase $register, bool $handleCancelled){
		$this->listener = new PacketMonitorListener($register, $handleCancelled);
	}

	public function monitorIncoming(Closure $handler): IPacketMonitor{
		$this->listener->monitorIncoming($handler);
		return $this;
	}

	public function monitorOutgoing(Closure $handler): IPacketMonitor{
		$this->listener->monitorOutgoing($handler);
		return $this;
	}

	public function unregisterIncomingMonitor(Closure $handler): IPacketMonitor{
		$this->listener->unregisterIncomingMonitor($handler);
		return $this;
	}

	public function unregisterOutgoingMonitor(Closure $handler): IPacketMonitor{
		$this->listener->unregisterOutgoingMonitor($handler);
		return $this;
	}

	public function unregisterAll(): IPacketMonitor{
		$this->listener->unregisterAll();
		return $this;
	}
}
