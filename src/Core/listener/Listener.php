<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core\listener;
use HQGames\Core\fakeblocks\FakeBlockManager;
use HQGames\Core\Options;
use HQGames\Core\player\details\Scoreboard;
use HQGames\Core\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\world\World;


/**
 * Class Listener
 * @package HQGames\Core\listener
 * @author Jan Sohn / xxAROX
 * @date 05. July, 2022 - 17:11
 * @ide PhpStorm
 * @project Core
 */
class Listener implements \pocketmine\event\Listener{
	/**
	 * Function PlayerCreationEvent
	 * @param PlayerCreationEvent $event
	 * @return void
	 * @priority MONITOR
	 */
	function PlayerCreationEvent(PlayerCreationEvent $event): void{
		$event->setPlayerClass(Options::$player_class);
	}

	/**
	 * Function PlayerJoinEvent
	 * @param PlayerJoinEvent $event
	 * @return void
	 */
	function PlayerJoinEvent(PlayerJoinEvent $event): void{
		$event->setJoinMessage("");

		/** @var Player $player */
		$player = $event->getPlayer();
		$scoreboard = new Scoreboard("title");
		$scoreboard->addLine("<toast><logo>content</toast>");
		$player->setScoreboard($scoreboard);

		//TESTING
		$fakeBlock = FakeBlockManager::getInstance()->createFakeBlock(VanillaBlocks::COAL_ORE(), $event->getPlayer()->getPosition());
		var_dump($fakeBlock->getViewers());
	}

	/**
	 * Function PlayerQuitEvent
	 * @param PlayerQuitEvent $event
	 * @return void
	 */
	function PlayerQuitEvent(PlayerQuitEvent $event): void{
		$event->setQuitMessage("");
	}

	/**
	 * Function WorldLoadEvent
	 * @param WorldLoadEvent $event
	 * @return void
	 */
	function WorldLoadEvent(WorldLoadEvent $event): void{
		$event->getWorld()->setTime(World::TIME_NOON);
		$event->getWorld()->stopTime();
	}

	/**
	 * Function PlayerInteractEvent
	 * @param PlayerInteractEvent $event
	 * @return void
	 */
	function PlayerInteractEvent(PlayerInteractEvent $event): void{
		if ($event->getAction() == PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
			$event->getPlayer()->getNetworkSession()->sendDataPacket(AnimatePacket::create($event->getPlayer()->getId(), AnimatePacket::ACTION_SWING_ARM));
		}
	}
}
