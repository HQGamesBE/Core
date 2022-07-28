<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core\commands;
use HQGames\Permissions;
use HQGames\Core\player\Player;
use HQGames\Core\Tests;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;


/**
 * Class TestCommand
 * @package HQGames\Core\commands
 * @author Jan Sohn / xxAROX
 * @date 14. July, 2022 - 00:58
 * @ide PhpStorm
 * @project Core
 */
class TestCommand extends Command{
	/**
	 * TestCommand constructor.
	 */
	public function __construct(){
		parent::__construct("test", "Test command", "/test help", []);
		$this->setPermission(Permissions::COMMAND_TEST[0]);
	}

	/**
	 * Function execute
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return bool
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
		if (!$sender instanceof Player) {
			$sender->sendMessage("This command can only be used in-game.");
			return true;
		}
		if (!$sender->hasPermission($this->getPermission())) throw new CommandException("You don't have permission to use this command.");
		$sender->sendForm(Tests::getInstance()->getForm());
		return true;
	}
}
