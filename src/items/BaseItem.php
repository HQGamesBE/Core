<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core\items;
use Closure;
use DaveRandom\CallbackValidator\BuiltInTypes;
use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\InvalidCallbackException;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\SimpleInventory;
use pocketmine\item\Item;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use TypeError;


/**
 * Class BaseItem
 * @package HQGames\Core\items
 * @author Jan Sohn / xxAROX
 * @date 20. July, 2022 - 19:37
 * @ide PhpStorm
 * @project Core
 */
trait BaseItem{
	private Player $holder;
	private array $inventories = [];
	private ?Closure $entityInteractCallback = null; // Closure(Player $player, Entity $entity, Block $block): bool
	private ?Closure $entityAttackCallback = null; // Closure(Player $player, Entity $victim): bool
	private ?Closure $blockInteractCallback = null; // Closure(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): bool
	private ?Closure $blockDestroyCallback = null; // Closure(Player $player, Block $block): bool
	private ?Closure $clickAirCallback = null; // Closure(Player $player, Vector3 $directionVector): ItemUseResult
	private ?Closure $releaseUsingCallback = null; // Closure(Player $player): ItemUseResult
	private ?Closure $useCallback = null; // Closure(Player $player): void
	private ?CallbackInventoryListener $callbackInventoryListener = null;

	/**
	 * Function getCooldownTicks
	 * @description Returns the number of ticks a player must wait before activating this item again.
	 * @return int
	 */
	public function getCooldownTicks(): int{
		return 10;
	}

	/**
	 * Function onClickAir
	 * @param Player $player
	 * @param Vector3 $directionVector
	 * @return ItemUseResult
	 */
	public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult{
		/** @var Item $this */
		$result = ($this->holder->hasItemCooldown($this) ? ItemUseResult::FAIL() : ItemUseResult::SUCCESS());
		if (!is_null($this->useCallback)) ($this->useCallback)($this->holder);
		if (!is_null($this->clickAirCallback)) $result = ($this->clickAirCallback)($this->holder, $directionVector);
		$this->holder->resetItemCooldown($this, 10);
		return $result;
	}

	/**
	 * Function onInteractBlock
	 * @param Player $player
	 * @param Block $blockReplace
	 * @param Block $blockClicked
	 * @param int $face
	 * @param Vector3 $clickVector
	 * @return ItemUseResult
	 */
	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): ItemUseResult{
		/** @var Item $this */
		$result = ($this->holder->hasItemCooldown($this) ? ItemUseResult::FAIL() : ItemUseResult::SUCCESS());
		if (!is_null($this->useCallback)) ($this->useCallback)($this->holder);
		if (!is_null($this->blockInteractCallback)) $result = ($this->blockInteractCallback)($this->holder, $blockReplace, $blockClicked, $face, $clickVector);
		$this->holder->resetItemCooldown($this, 10);
		return $result;
	}

	/**
	 * Function onDestroyBlock
	 * @param Block $block
	 * @return bool
	 */
	public function onDestroyBlock(Block $block): bool{
		/** @var Item $this */
		$result = !$this->holder->hasItemCooldown($this);
		if (!is_null($this->blockDestroyCallback)) $result = ($this->blockDestroyCallback)($this->holder);
		$this->holder->resetItemCooldown($this, 10);
		return $result;
	}

	/**
	 * Function onReleaseUsing
	 * @param Player $player
	 * @return ItemUseResult
	 */
	public function onReleaseUsing(Player $player): ItemUseResult{
		/** @var Item $this */
		$result = ($this->holder->hasItemCooldown($this) ? ItemUseResult::FAIL() : ItemUseResult::SUCCESS());
		if (!is_null($this->useCallback)) ($this->useCallback)($this->holder);
		if (!is_null($this->releaseUsingCallback)) $result = ($this->releaseUsingCallback)($this->holder);
		$this->holder->resetItemCooldown($this, 10);
		return $result;
	}

	/**
	 * Function onAttackEntity
	 * @param Entity $victim
	 * @return bool
	 */
	public function onAttackEntity(Entity $victim): bool{
		/** @var Item $this */
		$result = !$this->holder->hasItemCooldown($this);
		if (!is_null($this->useCallback)) ($this->useCallback)();
		if (!is_null($this->entityAttackCallback)) $result = ($this->entityAttackCallback)($this->holder, $victim);
		$this->holder->resetItemCooldown($this, 10);
		return $result;
	}

	/**
	 * Function onInteractEntity
	 * @param Player $player
	 * @param Entity $entity
	 * @return bool
	 */
	public function onInteractEntity(Player $player, Entity $entity): bool{
		/** @var Item $this */
		$result = !$this->holder->hasItemCooldown($this);
		if (!is_null($this->useCallback)) ($this->useCallback)();
		if (!is_null($this->entityInteractCallback)) $result = ($this->entityInteractCallback)($this->holder, $entity);
		$this->holder->resetItemCooldown($this, 10);
		return $result;
	}

	/**
	 * Function init
	 * @param Player $holder
	 * @param string $custom_name
	 * @return void
	 */
	protected function init(Player $holder, string $custom_name = ""){
		/** @var Item $this */
		$this->holder = $holder;
		$this->inventories = [ $this->holder->getInventory(), $this->holder->getOffHandInventory(), $this->holder->getCursorInventory(), $this->holder->getArmorInventory(), $this->holder->getCraftingGrid(), $this->holder->getEnderInventory() ];
		$this->setCustomName("§r${custom_name}§r");
	}

	/**
	 * Function getHolder
	 * @return Player
	 */
	public function getHolder(): Player{
		return $this->holder;
	}

	/**
	 * Function testCallback
	 * @param CallbackType $callbackType
	 * @param null|callable|Closure $callback
	 * @return void
	 */
	private function testCallback(CallbackType $callbackType, null|callable|Closure $callback): void{
		if (is_null($callback)) return;
		if (is_callable($callback)) $callback = Closure::fromCallable($callback);
		try {
			Utils::validateCallableSignature($callbackType, $callback);
		} catch (TypeError | InvalidCallbackException $e) {
			throw new InvalidCallbackException($e->getMessage());
		}
	}

	/**
	 * Function setUseCallback
	 * @param null|Closure $callback (Player $player): void
	 * @return static
	 */
	public function setUseCallback(?Closure $callback = null): static{
		$this->testCallback(new CallbackType(
			new ReturnType(),
			new ParameterType("player", Player::class)
		), $callback);
		$this->useCallback = $callback;
		return $this;
	}

	/**
	 * Function setEntityInteractCallback
	 * @param null|Closure $callback (Player $player, Entity $entity): bool
	 * @return $this
	 */
	public function setEntityInteractCallback(?Closure $callback = null): static{
		$this->testCallback(new CallbackType(
			new ReturnType(BuiltInTypes::BOOL),
			new ParameterType("player", Player::class),
			new ParameterType("entity", Entity::class),
		), $callback);
		$this->entityInteractCallback = $callback;
		return $this;
	}

	/**
	 * Function setEntityAttackCallback
	 * @param null|Closure $callback (Player $player, Entity $entity): bool
	 * @return $this
	 */
	public function setEntityAttackCallback(?Closure $callback = null): static{
		$this->testCallback(new CallbackType(
			new ReturnType(BuiltInTypes::BOOL),
			new ParameterType("player", Player::class),
			new ParameterType("entity", Entity::class),
		), $callback);
		$this->entityAttackCallback = $callback;
		return $this;
	}

	/**
	 * Function setBlockInteractCallback
	 * @param null|Closure $callback (Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): bool
	 * @return $this
	 */
	public function setBlockInteractCallback(?Closure $callback = null): static{
		$this->testCallback(new CallbackType(
			new ReturnType(BuiltInTypes::BOOL),
			new ParameterType("player", Player::class),
			new ParameterType("blockReplace", Block::class),
			new ParameterType("blockClicked", Block::class),
			new ParameterType("face", BuiltInTypes::INT),
			new ParameterType("clickVector", Vector3::class),
		), $callback);
		$this->blockInteractCallback = $callback;
		return $this;
	}

	/**
	 * Function setBlockDestroyCallback
	 * @param null|Closure $callback (Player $player, Block $block): bool
	 * @return $this
	 */
	public function setBlockDestroyCallback(?Closure $callback = null): static{
		$this->testCallback(new CallbackType(
			new ReturnType(BuiltInTypes::BOOL),
			new ParameterType("player", Player::class),
			new ParameterType("block", Block::class),
		), $callback);
		$this->blockDestroyCallback = $callback;
		return $this;
	}

	/**
	 * Function setClickAirCallback
	 * @param null|Closure $callback (Player $player, Vector3 $clickVector): ItemUseResult
	 * @return $this
	 */
	public function setClickAirCallback(?Closure $callback = null): static{
		$this->testCallback(new CallbackType(
			new ReturnType(ItemUseResult::class),
			new ParameterType("player", Player::class),
			new ParameterType("directionVector", Vector3::class),
		), $callback);
		$this->clickAirCallback = $callback;
		return $this;
	}

	/**
	 * Function setReleaseUsingCallback
	 * @param null|Closure $callback (Player $player): ItemUseResult
	 * @return $this
	 */
	public function setReleaseUsingCallback(?Closure $callback = null): static{
		$this->testCallback(new CallbackType(
			new ReturnType(ItemUseResult::class),
			new ParameterType("player", Player::class),
		), $callback);
		$this->releaseUsingCallback = $callback;
		return $this;
	}

	/**
	 * Function setCallbackInventoryListener
	 * @param null|CallbackInventoryListener $callbackInventoryListener
	 * @return $this
	 */
	public function setCallbackInventoryListener(CallbackInventoryListener $callbackInventoryListener = null): static{
		if (is_null($this->callbackInventoryListener) && is_null($callbackInventoryListener)) return $this;
		$this->callbackInventoryListener = $callbackInventoryListener;
		/** @var SimpleInventory $inventory */
		foreach ($this->inventories as $inventory) {
			if (!is_null($callbackInventoryListener))
				$inventory->getListeners()->add($this->callbackInventoryListener);
			else
				$inventory->getListeners()->remove($this->callbackInventoryListener);
		}
		return $this;
	}

	/**
	 * Function addInventory
	 * @param SimpleInventory $inventory
	 * @return static
	 */
	public function addInventory(SimpleInventory $inventory): static{
		$this->inventories[$inventory::class] = $inventory;
		return $this;
	}

	/**
	 * Function removeInventory
	 * @param SimpleInventory $inventory
	 * @return static
	 */
	public function removeInventory(SimpleInventory $inventory): static{
		unset($this->inventories[$inventory::class]);
		return $this;
	}
}