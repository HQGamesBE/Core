<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core\jumpandrun;
use HQGames\Core\player\Player;
use InvalidArgumentException;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;

use function HQGames\decodePosition;


/**
 * Class JumpAndRunManager
 * @package HQGames\Core\jumpandrun
 * @author Jan Sohn / xxAROX
 * @date 05. July, 2022 - 17:06
 * @ide PhpStorm
 * @project Core
 */
class JumpAndRunManager{
	use SingletonTrait;
	
	private bool $enabled = false;
	private array $start_positions = [];


	/**
	 * JumpAndRunManager constructor.
	 * @param string[] $positions
	 */
	public function __construct(array $positions) {
		self::setInstance($this);

		if (empty($positions))
			$this->enabled = false;
		else
			foreach ($positions as $position)
				$positions[] = decodePosition($position, false);
	}

	public function start(Player $player): JumpAndRun{
		if (!$this->enabled) throw new InvalidArgumentException("JumpAndRun is not enabled");

		$highscore = 0; // TODO: get from redis
		$jump_and_run = new JumpAndRun($player, $this->getJumpAndRunBlocks(), $highscore);
		return $jump_and_run;
	}

	/**
	 * Function getJumpAndRunBlocks
	 * @return JumpAndRunBlock[]
	 */
	private function getJumpAndRunBlocks(): array{
		return [
			new JumpAndRunBlock(VanillaBlocks::CONCRETE_POWDER()->setColor(DyeColor::getAll()[array_rand(DyeColor::getAll())]), VanillaBlocks::CONCRETE()->setColor(DyeColor::WHITE()), [-3, -2, 2, 3], 100, 0),
			new JumpAndRunBlock(VanillaBlocks::OAK_FENCE(), VanillaBlocks::SPRUCE_FENCE(), [-3, -2, 2, 3], 50, 10),
			new JumpAndRunBlock(VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::getAll()[array_rand(DyeColor::getAll())]), VanillaBlocks::BIRCH_FENCE(), [-2, 2], 50, 25),
		];
	}
}
