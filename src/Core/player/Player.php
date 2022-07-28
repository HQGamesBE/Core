<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core\player;
use HQGames\Bridge\player\BridgePlayer;
use HQGames\Core\entities\details\Corpse;
use HQGames\Core\Options;
use HQGames\Core\player\details\Scoreboard;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use xxAROX\xxTOOLS\arena\BaseSetup;
use xxAROX\xxTOOLS\entity\details\DamageIndicator;


/**
 * Class Player
 * @package HQGames\Core\player
 * @author Jan Sohn / xxAROX
 * @date 05. July, 2022 - 17:08
 * @ide PhpStorm
 * @project Core
 */
class Player extends BridgePlayer{
	protected ?Scoreboard $scoreboard = null;
	public ?BaseSetup $setup = null;
	public bool $coords = false;

	/**
	 * Function getBlocksAroundWithEntityInsideActions
	 * @return array
	 */
	protected function getBlocksAroundWithEntityInsideActions(): array{
		if ($this->blocksAround === null) {
			$inset = 0.001; //Offset against floating-point errors

			$minX = (int)floor($this->boundingBox->minX + $inset);
			$minY = (int)floor($this->boundingBox->minY + $inset - (($this->boundingBox->minY - ((int)$this->boundingBox->minY)) > 2 / 16 ? 0 : 1));
			$minZ = (int)floor($this->boundingBox->minZ + $inset);
			$maxX = (int)floor($this->boundingBox->maxX - $inset);
			$maxY = (int)floor($this->boundingBox->maxY - $inset);
			$maxZ = (int)floor($this->boundingBox->maxZ - $inset);

			$this->blocksAround = [];
			$world = $this->getWorld();

			for ($z = $minZ; $z <= $maxZ; ++$z) {
				for ($x = $minX; $x <= $maxX; ++$x) {
					for ($y = $minY; $y <= $maxY; ++$y) {
						$block = $world->getBlockAt($x, $y, $z);
						if ($block->hasEntityCollision()) {
							$this->blocksAround[] = $block;
						}
					}
				}
			}
		}
		return $this->blocksAround;
	}

	/**
	 * Function attack
	 * @param EntityDamageEvent $source
	 * @return void
	 */
	public function attack(EntityDamageEvent $source): void{
		parent::attack($source);
		if (!$source instanceof EntityDamageByEntityEvent) return;
		if ($source->isCancelled()) return;
		$damager = $source->getDamager();
		if (!$damager instanceof Player) return;
		$item = $damager->getInventory()->getItemInHand();
		if (!$source->isCancelled()) {
			$damageIndicator = new DamageIndicator($this, $damager, $source->getFinalDamage());
			if (!$damageIndicator->isClosed() && !is_null($damageIndicator->location) && $damageIndicator->location->isValid()) {
				$damageIndicator->spawnTo($damager);
			}
		}
	}

	/**
	 * Function onDeath
	 * @return void
	 */
	protected function onDeath(): void{
		if (Options::$enable_corps) {
			$e = new Corpse($this);
			$e->spawnToAll();
		}
		parent::onDeath();
	}

	/**
	 * Function sendLeftSideTitle
	 * @param string $content
	 * @param int $fadeIn
	 * @param int $stay
	 * @param int $fadeOut
	 * @return void
	 */
	public function sendLeftSideTitle(string $content, int $fadeIn = 1, int $stay = 18, int $fadeOut = 1): void{
		$this->sendTitle(" ", "§r" . str_repeat("\n", 6) . "§r" . $this->translate($content) . str_repeat(" ", 36), $fadeIn, $stay, $fadeOut);
	}

	/**
	 * Function sendRightSideTitle
	 * @param string $content
	 * @param int $fadeIn
	 * @param int $stay
	 * @param int $fadeOut
	 * @return void
	 */
	public function sendRightSideTitle(string $content, int $fadeIn = 1, int $stay = 18, int $fadeOut = 1): void{
		$this->sendTitle(" ", "§r" . str_repeat("\n", 6) . str_repeat(" ", 36) . "§r" . $this->translate($content), $fadeIn, $stay, $fadeOut);
	}

	/**
	 * Function setScoreboard
	 * @param Scoreboard $scoreboard
	 * @return void
	 */
	public function setScoreboard(Scoreboard $scoreboard): void{
		/*if (!$this->playerSettings->enable_scoreboard) {
			if (!is_null($this->scoreboard)) {
				$this->removeScoreboard();
			}
			return;
		}*/
		if ($this->scoreboard !== null) {
			if ($this->scoreboard === $scoreboard) {
				return;
			}
		}
		if ($this->scoreboard === null || $scoreboard->getTitle() !== $this->scoreboard->getTitle()) {
			$pk = new SetDisplayObjectivePacket();
			$pk->displaySlot = "sidebar";
			$pk->objectiveName = $scoreboard->getObjName();
			$pk->displayName = "{$scoreboard->getTitle()}";
			$pk->criteriaName = "dummy";
			$pk->sortOrder = 0;
			$this->getNetworkSession()->sendDataPacket($pk);
		}
		$entries = [];
		$player = $this;
		foreach ($scoreboard->getLines() as $num => $line) {
			if (empty($line)) {
				$line = str_repeat("\0", $num);
			}
			/*$line = preg_replace_callback("/%([a-zA-Z0-9_.]+)/", static function($match) use ($player) {
				return $player->translate($match[1]);
			}, $line);*/

			$entry = new ScorePacketEntry();
			$entry->objectiveName = $scoreboard->getObjName();
			$entry->type = ScorePacketEntry::TYPE_PLAYER;
			$entry->customName = " $line   ";
			$entry->score = $num;
			$entry->scoreboardId = $num;
			$entries[$num] = $entry;
		}
		if ($this->hasScoreboard()) {
			$rm = new SetScorePacket();
			$rm->type = SetScorePacket::TYPE_REMOVE;
			$rm->entries = $entries;
			$this->getNetworkSession()->sendDataPacket($rm);
		}
		$pk = new SetScorePacket();
		$pk->type = SetScorePacket::TYPE_CHANGE;
		$pk->entries = $entries;
		$this->getNetworkSession()->sendDataPacket($pk);
		$this->scoreboard = $scoreboard;
	}

	/**
	 * Function hasScoreboard
	 * @return bool
	 */
	public function hasScoreboard(): bool{
		return $this->scoreboard !== null;
	}

	/**
	 * Function getScoreboard
	 * @return null|Scoreboard
	 */
	public function getScoreboard(): ?Scoreboard{
		return $this->scoreboard;
	}

	/**
	 * Function removeScoreboard
	 * @return bool
	 */
	public function removeScoreboard(): bool{
		if ($this->hasScoreboard()) {
			$pk = new RemoveObjectivePacket();
			$pk->objectiveName = $this->scoreboard->getObjName();
			$this->getNetworkSession()->sendDataPacket($pk);
			$this->scoreboard = null;
			return true;
		}
		return false;
	}

	/**
	 * Function createCorp
	 * @return void
	 */
	public function createCorp(): void{
		$corp = new Corpse($this);
		$corp->spawnToAll();
	}

	/**
	 * Function playSound
	 * @param string $sound
	 * @param float $volume
	 * @param float $pitch
	 * @param null|string|Player[] $targets
	 * @return void
	 */
	public function playSound(string $sound, float $volume = 1.0, float $pitch = 1.0, array|string $targets = null): void{
		$pk = PlaySoundPacket::create($sound, $this->getPosition()->x, $this->getPosition()->y, $this->getPosition()->z, $volume, $pitch);
		if ($targets == "*") $targets = $this->getWorld()->getPlayers();
		$this->getServer()->broadcastPackets($targets ?? [$this], [$pk]);
	}

	/**
	 * Function playErrorSound
	 * @return void
	 */
	public function playErrorSound(): void{
		$this->playSound("block.lantern.break");
	}

	/**
	 * Function setCoords
	 * @param null|bool $value
	 * @return void
	 */
	public function setCoords(?bool $value = true): void{
		if ($this->coords == $value) return;
		$this->coords = $value;
		$this->getNetworkSession()->sendDataPacket(GameRulesChangedPacket::create([
			"showcoordinates" => new BoolGameRule($value, false)
		]));
	}

	/**
	 * Function hasCoords
	 * @return bool
	 */
	public function hasCoords(): bool{
		return $this->coords;
	}

	/**
	 * Function countItems
	 * @param int $itemId
	 * @return int
	 */
	public function countItems(int $itemId): int{
		$all = 0;
		$content = $this->getInventory()->getContents();
		foreach ($content as $item) {
			if ($item->getId() == $itemId) {
				$c = $item->getCount();
				$all = $all + $c;
			}
		}
		return $all;
	}

	/**
	 * Function removeItem
	 * @param Item $item
	 * @return void
	 */
	public function removeItem(Item $item){
		$this->getInventory()->removeItem(ItemFactory::getInstance()->get($item->getId(), $item->getMeta(), $item->getCount()));
	}

	/**
	 * Function winEffect
	 * @return void
	 */
	public function winEffect(): void{
		$itemBefore = $this->getOffHandInventory()->getItem(0);
		$this->getOffHandInventory()->setItem(0, VanillaItems::TOTEM()->setCustomName("Temporary item"));
		$this->broadcastAnimation(new TotemUseAnimation($this), [$this]);
		$this->broadcastSound(new TotemUseSound(), [$this]);
		$this->getOffHandInventory()->setItem(0, $itemBefore);
	}

	/**
	 * Function vanish
	 * @param bool $value
	 * @return void
	 */
	public function vanish(bool $value): void{
		$this->winEffect();
		if ($value) {
			foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
				if (!$onlinePlayer->hasPermission("xxarox.bypass.vanish")) {
					$onlinePlayer->hidePlayer($this);
				} else {
					if ($this->hasPermission("xxarox.bypass.vanish.owner")) {
						$onlinePlayer->hidePlayer($this);
					}
				}
				if ($onlinePlayer->getId() != $this->getId()) {
					$onlinePlayer->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($this->getUniqueId())]));
				}
			}
		} else {
			foreach ($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
				$onlinePlayer->showPlayer($this);
				$onlinePlayer->getNetworkSession()->sendDataPacket(PlayerListPacket::add([PlayerListEntry::createAdditionEntry($this->getUniqueId(), $this->getId(), $this->getDisplayName(), SkinAdapterSingleton::get()->toSkinData($this->getSkin()), $this->getXuid())]));
			}
		}
	}

	public function playScreenAnimation(int $effectId = ScreenAnimationEffectIds::LUCK): void{
		$this->getNetworkSession()->sendDataPacket(OnScreenTextureAnimationPacket::create($effectId));
	}
}
