<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core\jumpandrun;
use Closure;
use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use HQGames\addons\fakeblocks\FakeBlock;
use HQGames\addons\fakeblocks\FakeBlockManager;
use HQGames\Core\player\Player;
use pocketmine\block\Block;
use pocketmine\utils\Utils;
use pocketmine\world\particle\HappyVillagerParticle;


/**
 * Class JumpAndRunBlock
 * @package HQGames\Core\jumpandrun
 * @author Jan Sohn / xxAROX
 * @date 13. July, 2022 - 23:18
 * @ide PhpStorm
 * @project Core
 * @credits RyZerBE
 */
final class JumpAndRunBlock{
	private Block $block;
	private Block $blockForSuccess;
	private ?FakeBlock $dummy = null;
	/** @var int[] */
	private array $sides;
	private int $chance;
	private int $minimumScore;
	private ?Closure $reward;

	/**
	 * JumpAndRunBlock constructor.
	 * @param Block $block
	 * @param Block $blockForSuccess
	 * @param array $sides
	 * @param int $chance
	 * @param int $minimumScore
	 * @param null|Closure $reward
	 */
	public function __construct(Block $block, Block $blockForSuccess, array $sides, int $chance = 1, int $minimumScore = 0, Closure $reward = null){
		$this->block = $block;
		$this->blockForSuccess = $blockForSuccess;
		$this->sides = $sides;
		$this->chance = $chance;
		$this->minimumScore = $minimumScore;
		Utils::validateCallableSignature(new CallbackType(new ReturnType(), new ParameterType("player", Player::class)), $reward);
		$this->reward = $reward;
	}

	/**
	 * Function getBlock
	 * @return Block
	 */
	public function getBlock(): Block{
		return $this->block;
	}

	/**
	 * Function getBlockForSuccess
	 * @return Block
	 */
	public function getBlockForSuccess(): Block{
		return $this->blockForSuccess;
	}

	/**
	 * Function getSides
	 * @return int
	 */
	public function getSide(): int{
		return $this->sides[array_rand($this->sides)];
	}

	/**
	 * Function getChance
	 * @return int
	 */
	public function getChance(): int{
		return $this->chance;
	}

	/**
	 * Function getMinimumScore
	 * @return int
	 */
	public function getMinimumScore(): int{
		return $this->minimumScore;
	}

	/**
	 * Function success
	 * @param Player $player
	 * @return void
	 */
	public function success(Player $player): void{
		$pos = $this->dummy->getPosition();
		if (!is_null($this->dummy)) FakeBlockManager::getInstance()->destroy($this->dummy);
		$this->dummy = FakeBlockManager::getInstance()->create($this->getBlockForSuccess(), $pos, [$player]);

		for($i = 0; $i <= 10; $i++) {
			$tempPosition = $pos->floor()->add(0.5, 0.5, 0.5)->add((mt_rand(-10, 10) / 10), (mt_rand(-10, 10) / 10), (mt_rand(-10, 10) / 10));
			$pos->getWorld()->addParticle($tempPosition, new HappyVillagerParticle());
		}
		$this->reward($player);
	}

	/**
	 * Function reward
	 * @param Player $player
	 * @return void
	 */
	public function reward(Player $player): void{
		if ($this->reward !== null) ($this->reward)($player);
	}

	/**
	 * Function setDummy
	 * @param null|FakeBlock $dummy
	 * @return void
	 */
	public function setDummy(?FakeBlock $dummy = null): void{
		$this->dummy = $dummy;
	}

	/**
	 * Function getDummy
	 * @return ?FakeBlock
	 */
	public function getDummy(): ?FakeBlock{
		return $this->dummy;
	}
}
