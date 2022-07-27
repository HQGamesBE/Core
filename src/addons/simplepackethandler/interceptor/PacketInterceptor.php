<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\addons\simplepackethandler\interceptor;
use Closure;
use HQGames\Core\Core;
use JetBrains\PhpStorm\Pure;
use pocketmine\plugin\PluginBase;


/**
 * Class PacketInterceptor
 * @package HQGames\addons\simplepackethandler\interceptor
 * @author Jan Sohn / xxAROX
 * @date 05. July, 2022 - 00:31
 * @ide PhpStorm
 * @project Core
 */
class PacketInterceptor implements IPacketInterceptor{
	private PacketInterceptorListener $listener;

	#[Pure]
	public function __construct(PluginBase $register, int $priority, bool $handleCancelled){
		$this->listener = new PacketInterceptorListener($register, $priority, $handleCancelled);
	}

	public function interceptIncoming(Closure $handler) : IPacketInterceptor{
		$this->listener->interceptIncoming($handler);
		return $this;
	}

	public function interceptOutgoing(Closure $handler) : IPacketInterceptor{
		$this->listener->interceptOutgoing($handler);
		return $this;
	}

	public function unregisterIncomingInterceptor(Closure $handler) : IPacketInterceptor{
		$this->listener->unregisterIncomingInterceptor($handler);
		return $this;
	}

	public function unregisterOutgoingInterceptor(Closure $handler) : IPacketInterceptor{
		$this->listener->unregisterOutgoingInterceptor($handler);
		return $this;
	}

	/**
	 * Function unregisterAll
	 * @return IPacketInterceptor
	 */
	public function unregisterAll(): IPacketInterceptor{
		$this->listener->unregisterAll();
		return $this;
	}
}
