<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\Core\simplepackethandler\interceptor;
use Closure;
use pocketmine\network\mcpe\NetworkSession;


/**
 * Interface IPacketInterceptor
 * @package HQGames\Core\simplepackethandler\interceptor
 * @author Jan Sohn / xxAROX
 * @date 05. July, 2022 - 00:30
 * @ide PhpStorm
 * @project Core
 */
interface IPacketInterceptor{
	/**
	 * @template TServerboundPacket of ServerboundPacket
	 * @param Closure(TServerboundPacket, NetworkSession) : bool $handler
	 * @return IPacketInterceptor
	 */
	public function interceptIncoming(Closure $handler): IPacketInterceptor;

	/**
	 * @template TClientboundPacket of ClientboundPacket
	 * @param Closure(TClientboundPacket, NetworkSession) : bool $handler
	 * @return IPacketInterceptor
	 */
	public function interceptOutgoing(Closure $handler): IPacketInterceptor;

	/**
	 * @template TServerboundPacket of ServerboundPacket
	 * @param Closure(TServerboundPacket, NetworkSession) : bool $handler
	 * @return IPacketInterceptor
	 */
	public function unregisterIncomingInterceptor(Closure $handler): IPacketInterceptor;

	/**
	 * @template TClientboundPacket of ClientboundPacket
	 * @param Closure(TClientboundPacket, NetworkSession) : bool $handler
	 * @return IPacketInterceptor
	 */
	public function unregisterOutgoingInterceptor(Closure $handler): IPacketInterceptor;

	/**
	 * Function unregisterAll
	 * @return IPacketInterceptor
	 */
	public function unregisterAll(): IPacketInterceptor;
}
