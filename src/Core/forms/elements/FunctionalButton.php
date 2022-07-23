<?php
/*
 * Copyright (c) Jan Sohn
 * All rights reserved.
 * Only people with the explicit permission from Jan Sohn are allowed to modify, share or distribute this code.
 *
 * You are NOT allowed to do any kind of modification to this plugin.
 * You are NOT allowed to share this plugin with others without the explicit permission from Jan Sohn.
 * You are NOT allowed to run this plugin on your server as source code.
 * You MUST acquire this plugin from official sources.
 * You MUST run this plugin on your server as compiled .phar file from our releases.
 */
namespace HQGames\Core\forms\elements;
use Closure;
use JetBrains\PhpStorm\Pure;
use pocketmine\player\Player;


/**
 * Class FunctionalButton
 * @package HQGames\Core\forms\elements
 * @author xxAROX
 * @date 25.10.2020 - 02:26
 * @project PocketMine-Client
 * @internal
 */
class FunctionalButton extends Button{
	protected ?Closure $onClick = null;

	/**
	 * FunctionalButton constructor.
	 * @param string $text
	 * @param null|Closure $onClick
	 * @param null|Image $image
	 */
	#[Pure] public function __construct(string $text, ?Closure $onClick = null, ?Image $image = null){
		parent::__construct($text, $image);
		$this->onClick = $onClick;
	}

	/**
	 * Function onClick
	 * @param Player $player
	 * @return void
	 */
	public function onClick(Player $player): void{
		($this->onClick)($player);
	}
}
