<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\Core\fakeblocks;
use JetBrains\PhpStorm\Pure;
use pocketmine\block\Block;
use pocketmine\player\Player;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;


/**
 * Class FakeBlock
 * @package HQGames\Core\fakeblocks
 * @author Jan Sohn / xxAROX
 * @date 05. July, 2022 - 00:10
 * @ide PhpStorm
 * @project Core
 */
class FakeBlock{
	/** @var ?Player[] */
	protected ?array $viewers = null;
	/** @var Position[] */
	protected array $blockUpdatePacketQueue = [];

	/**
	 * FakeBlock constructor.
	 * @param Block $block
	 * @param Position $pos
	 * @param null|Player[] $viewers null = all players | array = players
	 */
	public function __construct(protected Block $block, Position $pos, ?array $viewers = null){
		$block->position($pos->getWorld(), (int)$pos->x, (int)$pos->y, (int)$pos->z);
		$this->viewers = $viewers;
	}

	/**
	 * Function getBlock
	 * @return Block
	 */
	public function getBlock(): Block{
		return $this->block;
	}

	/**
	 * Function getPosition
	 * @return Position
	 */
	#[Pure] public function getPosition(): Position{
		return $this->block->getPosition();
	}

	/**
	 * Function getViewers
	 * @return null|Player[]
	 */
	public function getViewers(): ?array{
		return $this->viewers;
	}

	/**
	 * Function isViewer
	 * @param Player $player
	 * @return bool
	 */
	#[Pure] public function isViewer(Player $player): bool{
		return is_null($this->viewers) || isset($this->viewers[$player->getId()]);
	}

	public function addViewer(Player $player): void{
		if (!is_null($this->viewers))
			$this->viewers[$player->getId()] = $player;
		$pos = $this->getPosition();
		if ($pos->getWorld() === $player->getWorld() && $player->isUsingChunk($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE)) {
			$packets = FakeBlockManager::getInstance()->createBlockUpdatePackets($player, [$this]);
			foreach ($packets as $packet)
				$player->getNetworkSession()->sendDataPacket($packet);
		}
	}

	public function removeViewer(Player $player): void{
		if (!is_null($this->viewers))
			unset($this->viewers[$player->getId()]);
		$pos = $this->getPosition();
		$world = $this->getPosition()->getWorld();
		if ($world === $player->getWorld() && $player->isUsingChunk($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE)) {
			$packets = FakeBlockManager::getInstance()->createBlockUpdatePackets($player, [$world->getBlock($pos)]);
			foreach ($packets as $packet)
				$player->getNetworkSession()->sendDataPacket($packet);
		}
	}

	/**
	 * Function isInBlockUpdatePacketQueue
	 * @param Player $player
	 * @return bool
	 * @internal
	 */
	#[Pure] public function isInBlockUpdatePacketQueue(Player $player): bool{
		return isset($this->blockUpdatePacketQueue[$player->getId()]);
	}

	/**
	 * Function blockUpdatePacketQueue
	 * @param Player $player
	 * @param bool $bool
	 * @return void
	 * @internal
	 */
	public function blockUpdatePacketQueue(Player $player, bool $bool): void{
		if ($bool) $this->blockUpdatePacketQueue[$player->getId()] = $player;
		else unset($this->blockUpdatePacketQueue[$player->getId()]);
	}
}
