<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\Core\fakeblocks;
use Exception;
use HQGames\Core\Core;
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
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;


/**
 * Class FakeBlockManager
 * @package HQGames\Core\fakeblocks
 * @author IvanCraft623
 * @date 05. July, 2022 - 00:09
 * @ide PhpStorm
 * @project Core
 */
class FakeBlockManager implements Listener{
	use SingletonTrait {
		setInstance as private;
		reset as private;
	}


	/** @var array<int, array<int, FakeBlock[]>> */
	private array $fakeblocks = [];

	private function __construct(){
		self::setInstance($this);
	}

	private static bool $isRegistered = false;

	public static function isRegistered(): bool{
		return self::$isRegistered;
	}

	public static function register(Core $registrant): void{
		if (self::$isRegistered)
			throw new Exception("FakeBlock listener is already registered by Core.");
		$instance = new self();
		$registrant->getServer()->getPluginManager()->registerEvents($instance, $registrant);
		$interceptor = SimplePacketHandler::createInterceptor($registrant, EventPriority::HIGHEST);
		$interceptor->interceptOutgoing(function (UpdateBlockPacket $packet, NetworkSession $target) use ($instance): bool{
			$player = $target->getPlayer();
			if ($player !== null) {
				$bpos = $packet->blockPosition;
				foreach ($instance->getFakeBlocksAtPosition(new Position($bpos->getX(), $bpos->getY(), $bpos->getZ(), $player->getWorld())) as $fakeblock) {
					if ($fakeblock->isViewer($player)) {
						if ($fakeblock->isInBlockUpdatePacketQueue($player)) {
							$fakeblock->blockUpdatePacketQueue($player, false);
						} else {
							return false;
						}
					}
				}
			}
			return true;
		});
	}

	public function create(Block $block, Position $position, ?array $viewers = null): FakeBlock{
		$pos = Position::fromObject($position->floor(), $position->getWorld());
		$fakeblock = new FakeBlock($block, $pos, $viewers);
		$chunkHash = World::chunkHash($pos->getFloorX() >> Chunk::COORD_BIT_SIZE, $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE);
		$this->fakeblocks[spl_object_id($pos->getWorld())][$chunkHash][spl_object_id($fakeblock)] = $fakeblock;
		return $fakeblock;
	}

	public function destroy(FakeBlock $fakeblock): void{
		foreach ($fakeblock->getViewers() as $viewer) {
			$fakeblock->removeViewer($viewer);
		}
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
	 * Returns the FakeBlocks in a Position
	 * @return FakeBlock[]
	 */
	public function getFakeBlocksAtPosition(Position $position): array{
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
	 * @param array<int, Block|FakeBlock> $blocks
	 *
	 * @return UpdateBlockPacket[]
	 */
	public function createBlockUpdatePackets(Player $player, array $blocks): array{
		$packets = [];
		$blockMapping = RuntimeBlockMapping::getInstance();
		foreach ($blocks as $b) {
			if ($b instanceof FakeBlock) {
				$b->blockUpdatePacketQueue($player, true);
				$fullId = $b->getBlock()->getFullId();
			} else if ($b instanceof Block) {
				$fullId = $b->getFullId();
			} else {
				throw new \TypeError("Expected Block or FakeBlock in blocks array, got " . (is_object($b)
						? get_class($b) : gettype($b)));
			}
			$blockPosition = BlockPosition::fromVector3($b->getPosition());
			$packets[] = UpdateBlockPacket::create($blockPosition, $blockMapping->toRuntimeId($fullId), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL);
		}
		return $packets;
	}

	public function onChunkSend(PlayerPostChunkSendEvent $event): void{
		$player = $event->getPlayer();
		$fakeblocks = [];
		foreach ($this->getFakeBlocksAt($player->getWorld(), $event->getChunkX(), $event->getChunkZ()) as $fakeblock) {
			if ($fakeblock->isViewer($player)) {
				$fakeblocks[] = $fakeblock;
			}
		}
		foreach ($this->createBlockUpdatePackets($player, $fakeblocks) as $packet) {
			$player->getNetworkSession()->sendDataPacket($packet);
		}
	}
}
