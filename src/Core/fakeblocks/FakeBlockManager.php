<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\Core\fakeblocks;
use Exception;
use HQGames\Core\addons\Addon;
use HQGames\Core\addons\AddonSingletonTrait;
use HQGames\Core\simplepackethandler\SimplePacketHandler;
use pocketmine\block\Block;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPostChunkSendEvent;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;
use TypeError;


/**
 * Class FakeBlockManager
 * @package HQGames\Core\fakeblocks
 * @author IvanCraft623
 * @date 05. July, 2022 - 00:09
 * @ide PhpStorm
 * @project Core
 */
class FakeBlockManager extends Addon implements Listener{
	use AddonSingletonTrait;
	/** @var array<int, array<int, FakeBlock[]>> */
	private array $fakeblocks = [];

	/**
	 * Function getVersion
	 * @return string
	 */
	public static function getVersion(): string{
		return "0.0.2";
	}

	/**
	 * Function getAuthors
	 * @return string[]
	 */
	public static function getAuthors(): array{
		return [
			"IvanCraft623",
			"xxAROX"
		];
	}

	public function onEnable(): void{
		$this->registerListener($this);
		$interceptor = SimplePacketHandler::getInstance()->createInterceptor(EventPriority::HIGHEST);
		$interceptor->interceptOutgoing(function (UpdateBlockPacket $packet, NetworkSession $target): bool{
			$player = $target->getPlayer();
			if ($player !== null) {
				$bpos = $packet->blockPosition;
				foreach (FakeBlockManager::getInstance()->getFakeBlocks(new Position($bpos->getX(), $bpos->getY(), $bpos->getZ(), $player->getWorld())) as $fakeblock) {
					if ($fakeblock->isViewer($player)) {
						if ($fakeblock->isInBlockUpdatePacketQueue($player))
							$fakeblock->blockUpdatePacketQueue($player, false);
						else
							return false;
					}
				}
			}
			return true;
		});
	}

	public function onDisable(): void{

	}

	public function createFakeBlock(Block $block, Position $position, ?array $viewers = null): FakeBlock{
		$pos = Position::fromObject($position->floor(), $position->getWorld());
		$fakeblock = new FakeBlock($block, $pos, $viewers);
		$chunkHash = World::chunkHash($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE);
		$this->fakeblocks[spl_object_id($pos->getWorld())][$chunkHash][spl_object_id($fakeblock)] = $fakeblock;
		return $fakeblock;
	}

	/**
	 * Function destroy
	 * @param FakeBlock $fakeblock
	 * @return void
	 */
	public function destroyFakeBlock(FakeBlock $fakeblock): void{
		foreach ($fakeblock->getViewers() as $viewer) $fakeblock->removeViewer($viewer);
		$pos = $fakeblock->getPosition();
		$chunkHash = World::chunkHash($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE);
		unset($this->fakeblocks[spl_object_id($pos->getWorld())][$chunkHash][spl_object_id($fakeblock)]);
	}

	/**
	 * Returns the FakeBlocks in a Chunk
	 * @return FakeBlock[]
	 */
	public function getFakeBlocksAt(World $world, int $chunkX, int $chunkZ): array{
		return $this->fakeblocks[spl_object_id($world)][World::chunkHash($chunkX, $chunkZ)] ?? [];
	}

	/**
	 * Function getFakeBlocks
	 * @param Position $position
	 * @return FakeBlock[] NOTE: Multiple FakeBlocks can be exists at the same position.
	 */
	public function getFakeBlocks(Position $position): array{
		$pos = $position->floor();
		$fakeblocks = [];
		foreach ($this->getFakeBlocksAt($position->getWorld(), $pos->getX() >> Chunk::COORD_BIT_SIZE, $pos->getZ() >> Chunk::COORD_BIT_SIZE) as $fakeblock) {
			if ($fakeblock->getPosition()->equals($pos)) {
				$fakeblocks[] = $fakeblock;
			}
		}
		return $fakeblocks;
	}

	/**
	 * Function createBlockUpdatePackets
	 * @param Player $player
	 * @param array<int, Block|FakeBlock> $blocks
	 * @return UpdateBlockPacket[]
	 * @internal
	 */
	public function createBlockUpdatePackets(Player $player, array $blocks): array{
		$packets = [];
		$blockMapping = RuntimeBlockMapping::getInstance();
		foreach ($blocks as $b) {
			if ($b instanceof FakeBlock) {
				$b->blockUpdatePacketQueue($player, true);
				$fullId = $b->getBlock()->getFullId();
			} else if ($b instanceof Block)
				$fullId = $b->getFullId();
			else
				throw new TypeError("Expected Block or FakeBlock in blocks array, got " . (is_object($b) ? get_class($b) : gettype($b)));

			$blockPosition = BlockPosition::fromVector3($b->getPosition());
			$packets[] = UpdateBlockPacket::create($blockPosition, $blockMapping->toRuntimeId($fullId), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL);
		}
		return $packets;
	}

	/**
	 * Function PlayerPostChunkSendEvent
	 * @param PlayerPostChunkSendEvent $event
	 * @return void
	 * @priority MONITOR
	 */
	public function PlayerPostChunkSendEvent(PlayerPostChunkSendEvent $event): void{
		$player = $event->getPlayer();
		$fakeblocks = [];
		foreach ($this->getFakeBlocksAt($player->getWorld(), $event->getChunkX(), $event->getChunkZ()) as $fakeblock) {
			if ($fakeblock->isViewer($player)) $fakeblocks[] = $fakeblock;
		}
		foreach ($this->createBlockUpdatePackets($player, $fakeblocks) as $packet) $player->getNetworkSession()->sendDataPacket($packet);
	}
}
