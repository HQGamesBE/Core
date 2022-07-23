<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\Core\entities\details;
use HQGames\Core\player\Player;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\PlayerMetadataFlags;


/**
 * Class Corpse
 * @package HQGames\Core\entities\details
 * @author Jan Sohn / xxAROX
 * @date 12. July, 2022 - 15:22
 * @ide PhpStorm
 * @project Core
 */
class Corpse extends Human{
	private float|int $livingTicks = (20 * 15);

	/**
	 * Crop constructor.
	 * @param Player $player
	 */
	public function __construct(Player $player){
		$nbt = new CompoundTag();
		$nbt->setTag("Pos", new ListTag([
			new DoubleTag($player->getPosition()->getX()),
			new DoubleTag($player->getPosition()->getY() - 1),
			new DoubleTag($player->getPosition()->getZ()),
		]));
		$nbt->setTag("Motion", new ListTag([
			new DoubleTag(0),
			new DoubleTag(0),
			new DoubleTag(0),
		]));
		$nbt->setTag("Rotation", new ListTag([
			new FloatTag($player->getLocation()->yaw),
			new FloatTag($player->getLocation()->pitch),
		]));
		parent::__construct($player->getLocation(), $player->getSkin(), $nbt);
		$this->setCanSaveWithChunk(false);
		$this->setHealth(1);
		$this->getNetworkProperties()->setBlockPos(EntityMetadataProperties::PLAYER_BED_POSITION, BlockPosition::fromVector3($player->getLocation()->asVector3()));
		$this->getNetworkProperties()->setPlayerFlag(PlayerMetadataFlags::SLEEP, true);
	}

	/**
	 * Function entityBaseTick
	 * @param int $tickDiff
	 * @return bool
	 */
	public function entityBaseTick(int $tickDiff = 1): bool{
		$parent = parent::entityBaseTick($tickDiff);
		$this->livingTicks--;
		if ($this->livingTicks == 0) {
			$this->flagForDespawn();
			return true;
		}
		return $parent;
	}

	/**
	 * Function canSaveWithChunk
	 * @return bool
	 */
	public function canSaveWithChunk(): bool{
		return false;
	}

	/**
	 * Function attack
	 * @param EntityDamageEvent $source
	 * @return void
	 */
	public function attack(EntityDamageEvent $source): void{
		$source->cancel();
		parent::attack($source);
	}
}
