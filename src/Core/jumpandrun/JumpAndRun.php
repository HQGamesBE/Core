<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core\jumpandrun;
use HQGames\Core\fakeblocks\FakeBlock;
use HQGames\Core\fakeblocks\FakeBlockManager;
use HQGames\Core\player\Player;
use JetBrains\PhpStorm\Pure;
use pocketmine\math\Vector3;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\Position;


/**
 * Class JumpAndRun
 * @package HQGames\Core\jumpandrun
 * @author Jan Sohn / xxAROX
 * @date 12. July, 2022 - 20:17
 * @ide PhpStorm
 * @project Core
 */
final class JumpAndRun{
	protected Player $player;
	protected int $highscore = 0;
	protected int $score = 0;
	protected ?JumpAndRunBlock $current_block = null;
	protected ?JumpAndRunBlock $next_block = null;

	/** @var JumpAndRunBlock[] */
	protected array $block_collection;

	/**
	 * JumpAndRun constructor.
	 * @param Player $player
	 * @param JumpAndRunBlock[] $block_collection
	 * @param int $highscore
	 */
	public function __construct(Player $player, array $block_collection, int $highscore = 0){
		$this->player = $player;
		$this->highscore = $highscore;
		$this->block_collection = $block_collection;
	}

	/**
	 * Function stepOn
	 * @return void
	 */
	public function check(): void{
		if ($this->player->getPosition()->y <= $this->next_block->getDummy()?->getPosition()->floor()->y) {
			$this->player->playSound("block.false_permissions");
			// TODO: send message to player that includes his score
			// TODO: send message to player that includes his highscore
			// TODO: save score to redis if its higher than his highscore
			// TODO: destroy the jump and run instance
			$this->player->teleport($this->player->getSpawn());
		}
		else if ($this->player->isOnGround() && $this->player->getPosition()->floor()->down()->equals($this->next_block->getDummy()?->getPosition())) { // NOTE: player is on ground and is standing on the next block
			$this->score++;
			$this->player->playSound("random.orb");
			$this->player->sendActionBarMessage("§gScore: §f{$this->score}");
			$this->next();
		}
	}

	/**
	 * Function next
	 * @return void
	 */
	protected function next(): void{
		$old_current = $this->current_block;
		$old_next = $this->next_block;
		if (!is_null($old_current)) $this->destroyCurrentBlock();
		$old_next->success($this->player);
		$this->current_block = $this->next_block;
		$next = $this->makeNextJumpAndRunBlock();
		if (!is_null($next)) $this->next_block = $next;
		else {
			$this->next_block = null;
			return;
		}
	}

	protected function destroyCurrentBlock(): void{
		FakeBlockManager::getInstance()->destroy($this->current_block->getDummy());
		$this->current_block->getDummy()?->getPosition()->getWorld()->addParticle($this->current_block->getDummy()?->getPosition(), new BlockBreakParticle($this->current_block->getBlock()), [$this->player]);
		$this->current_block->setDummy();
	}

	/**
	 * Function makeNextJumpAndRunBlock
	 * @return ?JumpAndRunBlock
	 */
	protected function makeNextJumpAndRunBlock(): ?JumpAndRunBlock{
		$pos = $this->current_block->getDummy()->getPosition();
		$left = 100;
		$position = new Position($pos->getFloorX() +$this->current_block->getSide(), $pos->getFloorY() +(is_null($this->current_block) ? 0 : -1), $pos->getFloorZ() +$this->current_block->getSide(), $pos->getWorld());
		while(
			(
				$pos->getWorld()->getBlock($position)->getId() !== 0
				|| $pos->getWorld()->getBlock($position->add(0, 1, 0))->getId() !== 0
				|| $pos->getWorld()->getBlock($position->add(0, 2, 0))->getId() !== 0
				|| $position->y >= 240
			) && $left > 0
		) {
			$position = $position->withComponents($pos->getFloorX() +$this->current_block->getSide(), $pos->getFloorY() +(is_null($this->current_block) ? 0 : -1), $pos->getFloorZ() +$this->current_block->getSide());
			$left--;
		}
		$floorPosition = new Position($position->floor()->x, $position->floor()->y, $position->floor()->z, $pos->getWorld());
		if ($left <= 0) return null;
		$jump_and_run_block = $this->chooseNextJumpAndRunBlock();
		$jump_and_run_block->setDummy(FakeBlockManager::getInstance()->create($jump_and_run_block->getBlock(), $floorPosition, [$this->player]));
		return $jump_and_run_block;
	}

	/**
	 * Function generateNextJumpAndRunBlock
	 * @return JumpAndRunBlock
	 */
	#[Pure] protected function chooseNextJumpAndRunBlock(): JumpAndRunBlock{
		$jump_and_run_block = $this->block_collection[array_rand($this->block_collection)];
		while($jump_and_run_block->getMinimumScore() > $this->score) $jump_and_run_block = $this->block_collection[array_rand($this->block_collection)];
		return $jump_and_run_block;
	}

	/**
	 * Function getHighscore
	 * @return int
	 */
	public function getHighscore(): int{
		return $this->highscore;
	}

	/**
	 * Function getScore
	 * @return int
	 */
	public function getScore(): int{
		return $this->score;
	}

	/**
	 * Function getPlayer
	 * @return Player
	 */
	public function getPlayer(): Player{
		return $this->player;
	}

	/**
	 * Function getCurrentBlock
	 * @return ?JumpAndRunBlock
	 */
	public function getCurrentBlock(): ?JumpAndRunBlock{
		return $this->current_block;
	}

	/**
	 * Function getNextBlock
	 * @return ?JumpAndRunBlock
	 */
	public function getNextBlock(): ?JumpAndRunBlock{
		return $this->next_block;
	}
}
