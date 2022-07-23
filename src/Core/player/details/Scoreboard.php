<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core\player\details;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;


/**
 * Class Scoreboard
 * @package HQGames\Core\player\details
 * @author Jan Sohn / xxAROX
 * @date 12. July, 2022 - 15:23
 * @ide PhpStorm
 * @project Core
 */
class Scoreboard{
	/** @var string */
	protected $objName;
	/** @var string */
	protected $title;
	/** @var string[] */
	protected $lines = [];


	/**
	 * Scoreboard constructor.
	 * @param string $title
	 * @param null|string $objName
	 */
	public function __construct(string $title = "", ?string $objName = null){
		$this->objName = $objName ?? "defaultObject";
		$this->title = $title;
	}

	/**
	 * Function getObjName
	 * @return string
	 */
	public function getObjName(): string{
		return $this->objName;
	}

	/**
	 * Function setTitle
	 * @param string $title
	 * @return void
	 */
	public function setTitle(string $title): void{
		$this->title = $title;
	}

	/**
	 * Function getTitle
	 * @return string
	 */
	public function getTitle(): string{
		return $this->title;
	}

	/**
	 * Function setLine
	 * @param int $key
	 * @param string $line
	 * @return void
	 */
	public function setLine(int $key, string $line): void{
		$this->lines[$key] = $line;
	}

	/**
	 * Function getLine
	 * @param int $key
	 * @return string
	 */
	public function getLine(int $key): string{
		return $this->lines[$key];
	}

	/**
	 * Function setLine
	 * @param string[] $lines
	 * @return void
	 */
	public function setLines(array $lines): void{
		$this->lines = array_values($lines);
	}

	/**
	 * Function getLines
	 * @return array
	 */
	public function getLines(): array{
		return $this->lines;
	}

	/**
	 * Function addLine
	 * @param string $line
	 * @return void
	 */
	public function addLine(string $line): void{
		$this->lines[] = $line;
	}
}
