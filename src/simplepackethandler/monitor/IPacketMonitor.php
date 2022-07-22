<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\Core\simplepackethandler\monitor;
use Closure;
use pocketmine\network\mcpe\NetworkSession;


/**
 * Interface IPacketMonitor
 * @package HQGames\Core\simplepackethandler\monitor
 * @author Jan Sohn / xxAROX
 * @date 05. July, 2022 - 00:51
 * @ide PhpStorm
 * @project Core
 */
interface IPacketMonitor{
	/**
	 * @template TServerboundPacket of ServerboundPacket
	 * @param Closure(TServerboundPacket, NetworkSession) : void $handler
	 * @return IPacketMonitor
	 */
	public function monitorIncoming(Closure $handler): IPacketMonitor;

	/**
	 * @template TClientboundPacket of ClientboundPacket
	 * @param Closure(TClientboundPacket, NetworkSession) : void $handler
	 * @return IPacketMonitor
	 */
	public function monitorOutgoing(Closure $handler): IPacketMonitor;

	/**
	 * @template TServerboundPacket of ServerboundPacket
	 * @param Closure(TServerboundPacket, NetworkSession) : void $handler
	 * @return IPacketMonitor
	 */
	public function unregisterIncomingMonitor(Closure $handler): IPacketMonitor;

	/**
	 * @template TClientboundPacket of ClientboundPacket
	 * @param Closure(TClientboundPacket, NetworkSession) : void $handler
	 * @return IPacketMonitor
	 */
	public function unregisterOutgoingMonitor(Closure $handler): IPacketMonitor;

	/**
	 * Function unregisterAll
	 * @return IPacketMonitor
	 */
	public function unregisterAll(): IPacketMonitor;
}
