<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core\entities\details;
use HQGames\Core\player\Player;
use JetBrains\PhpStorm\Pure;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;


/**
 * Class DamageIndicator
 * @package HQGames\Core\entities\details
 * @author Jan Sohn / xxAROX
 * @date 12. July, 2022 - 15:19
 * @ide PhpStorm
 * @project Core
 */
class DamageIndicator extends Entity{
	private int $age = 20;
	protected $gravity = 0;
	protected $drag = 0.02;

	/**
	 * Function getInitialSizeInfo
	 * @return EntitySizeInfo
	 */
	#[Pure] protected function getInitialSizeInfo(): EntitySizeInfo{
		return new EntitySizeInfo(0,0,0);
	}

	/**
	 * Function getNetworkTypeId
	 * @return string
	 */
	public static function getNetworkTypeId(): string{
		return EntityIds::SLIME;
	}

	/**
	 * DamageIndicator constructor.
	 * @param Entity $target
	 * @param Player $owner
	 * @param int|float $damage
	 */
	public function __construct(Entity $target, private Player $owner, int|float $damage = 1){
		$damage = round($damage, 1);
		parent::__construct(new Location($target->getLocation()->x, $target->getLocation()->y +1, $target->getLocation()->z, $target->getLocation()->world, $target->getLocation()->yaw, $target->getLocation()->pitch), CompoundTag::create());
		$this->setNameTag("ยงc${damage}");
		$this->setNameTagAlwaysVisible(true);
	}

	/**
	 * Function initEntity
	 * @param CompoundTag $nbt
	 * @return void
	 */
	protected function initEntity(CompoundTag $nbt): void{
		$this->setCanSaveWithChunk(false);
		parent::initEntity($nbt);
		$this->scale = 0;
		$this->size = new EntitySizeInfo(0, 0, 0);

		if (!$this->owner->getPlayerSettings()->enable_damage_indicator) {
			$this->flagForDespawn();
			return;
		}
		if ($this->owner->getPlayerSettings()->damage_indicator_spread) {
			$this->gravity = 0.03;
			$this->setMotion(new Vector3(lcg_value() * 0.2 -0.1, 0.01, lcg_value() * 0.2 -0.1));
		}
	}

	/**
	 * Function entityBaseTick
	 * @param int $tickDiff
	 * @return bool
	 */
	protected function entityBaseTick(int $tickDiff = 1): bool{
		$parent = parent::entityBaseTick($tickDiff);
		if ($this->age <= 0) {
			$this->flagForDespawn();
			return false;
		}
		$this->age -= $tickDiff;
		return $parent;
	}

	public function attack(EntityDamageEvent $source): void{
		$source->cancel();
	}
}
