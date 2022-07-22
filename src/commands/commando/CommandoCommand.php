<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\Core\commands\commando;
use HQGames\Bridge\Bridge;
use HQGames\Bridge\Cache;
use HQGames\Bridge\player\BridgePlayer;
use HQGames\Permissions;
use JetBrains\PhpStorm\Pure;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\lang\Translatable;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\Server;


/**
 * Class CommandoCommand
 * @package HQGames\Core\commands\commando
 * @author Jan Sohn / xxAROX
 * @date 22. July, 2022 - 19:58
 * @ide PhpStorm
 * @project Core
 */
abstract class CommandoCommand extends Command{
	/** White & Gray */
	const FLAG_NORMAL = 0;
	/** White & Aqua */
	const FLAG_AQUA = 1;
	/** White & ??? */
	const FLAG_TEST = 2;

	public const ARG_TYPE_INT             = 0x01;
	public const ARG_TYPE_FLOAT           = 0x03;
	public const ARG_TYPE_VALUE           = 0x04;
	public const ARG_TYPE_WILDCARD_INT    = 0x05;
	public const ARG_TYPE_OPERATOR        = 0x06;
	public const ARG_TYPE_TARGET          = 0x07;
	public const ARG_TYPE_WILDCARD_TARGET = 0x08;
	public const ARG_TYPE_FILEPATH = 0x10;
	public const ARG_TYPE_STRING   = 0x20;
	public const ARG_TYPE_POSITION = 0x28;
	public const ARG_TYPE_MESSAGE  = 0x2c;
	public const ARG_TYPE_RAWTEXT  = 0x2e;
	public const ARG_TYPE_JSON     = 0x32;
	public const ARG_TYPE_COMMAND  = 0x3f;

	private CommandData $commandData;
	private ?string $generalPermission = null;
	private bool $isPlayerCommand;
	private bool $isConsoleCommand;
	private bool $enableHelpArgument = true;
	private ?string $helpArgumentText = null;

	/**
	 * BaseCommand constructor.
	 * @param string $name
	 * @param Translatable|string $description
	 * @param null|Translatable|string $usageMessage
	 * @param array $aliases
	 * @param CommandParameter[][] $overloads
	 * @param bool $isConsoleCommand
	 * @param bool $isPlayerCommand
	 */
	public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = [], array $overloads = [], bool $isConsoleCommand = true, bool $isPlayerCommand = true){
		if (str_starts_with("%command.description.", $description) || str_starts_with("command.description.", $description)) {
			$description = "hqgames.command.description." . Cache::getInstance()->secret . "." . str_replace("%", "", $description);
		}
		if (strlen($description) > 0 and $description[0] == "%") {
			$description = Server::getInstance()->getLanguage()->translateString($description);
		}
		$this->isPlayerCommand = $isPlayerCommand;
		$this->isConsoleCommand = $isConsoleCommand;
		parent::__construct($name, $description, $usageMessage, $aliases);

		$this->commandData = new CommandData(
			strtolower($name),
			$description,
			self::FLAG_NORMAL,
			PlayerPermissions::VISITOR,
			(empty($this->getAliases()) ? null : new CommandEnum(ucfirst($this->getName()) . "Aliases", array_merge(array_values($this->getAliases()), [$this->getName()]))),
			[]
		);
		if ($this->enableHelpArgument) {
			$overloads = array_merge([[CommandParameter::enum("help", new CommandEnum("help", ["help","?"]), 0, true)]], $overloads ?? []);
		} else {
			$overloads = $overloads ?? [[CommandParameter::standard("arguments", self::ARG_TYPE_RAWTEXT, 0, true)]];
		}
		$this->commandData->overloads = $overloads;
	}

	/**
	 * Function testPermission
	 * @param CommandSender $target
	 * @param null|string $permission
	 * @return bool
	 */
	public final function testPermission(CommandSender $target, ?string $permission = null): bool{
		if ($this->testPermissionSilent($target, $permission)){
			return true;
		}
		if ($target instanceof BridgePlayer) {
			$target->sendMessage("%message.missingPermission");
		} else {
			$target->sendMessage(LanguageManager::get()->getLanguage()->translate("message.missingPermission"));
		}
		return false;
	}

	/**
	 * Function testPermissionSilent
	 * @param CommandSender $target
	 * @param null|string $permission
	 * @return bool
	 */
	public final function testPermissionSilent(CommandSender $target, ?string $permission = null): bool{
		$permission ??= $this->getPermission();
		if ($permission === null || $permission === "") return true;
		if ($target instanceof ConsoleCommandSender) return true;
		foreach (explode(";", $permission) as $p) {
			if ($target->hasPermission($p) || Server::getInstance()->isOp($target->getName())) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Function testForPlayer
	 * @param CommandSender $target
	 * @return bool
	 */
	public final function testForPlayer(CommandSender $target): bool{
		if ($this->testForPlayerSilent($target)) {
			return true;
		}
		if ($target instanceof BridgePlayer) {
			$target->sendMessage("%message.onlyPlayers");
		} else {
			$target->sendMessage(LanguageManager::get()->getLanguage()->translate("message.onlyPlayers"));
		}
		return false;
	}

	/**
	 * Function testForPlayerSilent
	 * @param CommandSender $target
	 * @return bool
	 */
	public final function testForPlayerSilent(CommandSender $target): bool{
		if (!$target instanceof BridgePlayer) {
			return false;
		}
		return true;
	}

	/**
	 * Function getGeneralPermission
	 * @return ?string
	 */
	public final function getGeneralPermission(): ?string{
		return $this->generalPermission;
	}

	/**
	 * Function setGeneralPermission
	 * @param null|string $permission
	 * @return void
	 */
	public final function setGeneralPermission(?string $permission): void{
		if (!is_null($permission)) {
			if (count($ex = explode(";", $permission)) > 1) {
				$permission = $ex[0];
			}
			if (PermissionManager::getInstance()->getPermission($permission) === null) {
				Permissions::getInstance()->registerPermission(new Permission($permission, "Allows to use the entire '/{$this->getName()}' command."));
			}
		}
		$this->generalPermission = $permission;
	}

	/**
	 * Function setPermission
	 * @param null|string $permission
	 * @return void
	 */
	public final function setPermission(?string $permission): void{
		if ($permission !== null) {
			$opRoot = PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_OPERATOR);
			foreach (explode(";", $permission) as $perm) {
				if (PermissionManager::getInstance()->getPermission($perm) === null) {
					PermissionManager::getInstance()->addPermission($perm = new Permission($perm, "Allows to use apart of the '/{$this->getName()}' command."));
					$opRoot->addChild($perm->getName(), true);
				}
			}
		}
		parent::setPermission($permission);
	}

	/**
	 * Function execute
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return void
	 */
	public final function execute(CommandSender $sender, string $commandLabel, array $args){
		if ($this->enableHelpArgument && isset($args[0]) && (strtolower($args[0]) == "help" || $args[0] == "?")) {
			$sender->sendMessage($this->helpArgumentText ?? ($sender instanceof BridgePlayer ? $sender->translate("%message.command.usage", [$this->getUsage()]) : "§cUsage: §7" . $this->getUsage()));
			return;
		}
		$this->onRun($sender, $commandLabel, $args);
	}

	/**
	 * Function onRun
	 * @param BridgePlayer|CommandSender $sender
	 * @param string $typedCommand
	 * @param array $args
	 * @return void
	 */
	abstract protected function onRun(BridgePlayer|CommandSender $sender, string $typedCommand, array $args): void;

	/**
	 * Function sendSyntaxError
	 * @param CommandSender $sender
	 * @param string $name
	 * @param string $at
	 * @param string $extra
	 * @param array $args
	 * @return void
	 */
	public function sendSyntaxError(CommandSender $sender, string $name, string $at, string $extra = "", array $args = []): void{
		$argsList = count($args) >= 1 ? " " . implode(" ", $args) : "";
		$sender->sendMessage("§cSyntax error: Unexpected \"$name\": at \"$at>>$extra<<$argsList\"");
	}

	/**
	 * Function getCommandData
	 * @return CommandData
	 */
	#[Pure] public function getCommandData() : CommandData{
		$data = clone $this->commandData;
		if (!empty($this->getAliases())) {
			$data->aliases = new CommandEnum(ucfirst($this->getName()) . "Aliases", array_merge(array_values($this->getAliases()), [$this->getName()]));
		}
		return $data;
	}

	/**
	 * Function addParameter
	 * @param CommandParameter $parameter
	 * @param int $overloadIndex
	 * @return void
	 */
	public function addParameter(CommandParameter $parameter, int $overloadIndex = 0) : void{
		$this->commandData->overloads[$overloadIndex][] = $parameter;
	}

	/**
	 * Function setParameter
	 * @param CommandParameter $parameter
	 * @param int $parameterIndex
	 * @param int $overloadIndex
	 * @return void
	 */
	public function setParameter(CommandParameter $parameter, int $parameterIndex, int $overloadIndex = 0) : void{
		$this->commandData->overloads[$overloadIndex][$parameterIndex] = $parameter;
	}

	/**
	 * Function setParameters
	 * @param array $parameters
	 * @param int $overloadIndex
	 * @return void
	 */
	public function setParameters(array $parameters, int $overloadIndex = 0) : void{
		$this->commandData->overloads[$overloadIndex] = array_values($parameters);
	}

	/**
	 * Function removeParameter
	 * @param int $parameterIndex
	 * @param int $overloadIndex
	 * @return void
	 */
	public function removeParameter(int $parameterIndex, int $overloadIndex = 0) : void{
		unset($this->commandData->overloads[$overloadIndex][$parameterIndex]);
	}

	/**
	 * Function removeAllParameters
	 * @return void
	 */
	public function removeAllParameters() : void{
		$this->commandData->overloads = [];
	}

	/**
	 * Function removeOverload
	 * @param int $overloadIndex
	 * @return void
	 */
	public function removeOverload(int $overloadIndex) : void{
		unset($this->commandData->overloads[$overloadIndex]);
	}

	/**
	 * Function getOverload
	 * @param int $index
	 * @return null|array
	 */
	public function getOverload(int $index) : ?array{
		return $this->commandData->overloads[$index] ?? null;
	}

	/**
	 * Function getOverloads
	 * @return array
	 */
	public function getOverloads() : array{
		return $this->commandData->overloads;
	}

	/**
	 * Function getPosition
	 * @param CommandSender $sender
	 * @param int $startIndex
	 * @param array $args
	 * @return null|Vector3
	 */
	public function getPosition(CommandSender $sender, int $startIndex, array $args): ?Vector3{
		if (isset($args[$startIndex])) {
			if (!isset($args[$startIndex + 2])) {
				$this->sendSyntaxError($sender, "", implode(" ", $args));
				return null;
			}
			$x = $args[$startIndex];
			$y = $args[$startIndex +1];
			$z = $args[$startIndex +2];

			if ($sender instanceof CorePlayer || $sender instanceof CommandBlockSender) {
				if (str_starts_with($x, "~")) {
					$offsetString = substr($x, 1);
					$negative = str_starts_with($offsetString, "-") || !str_starts_with($offsetString, "+");
					if ($negative) {
						$offsetString = substr($x, 1);
					}
					if (is_numeric($offsetString)) {
						$x = $sender->getPosition()->x + ($negative ? -floatval($offsetString) : floatval($offsetString));
					}
				}
				if (str_starts_with($y, "~")) {
					$offsetString = substr($y, 1);
					$negative = str_starts_with($offsetString, "-") || !str_starts_with($offsetString, "+");
					if ($negative) {
						$offsetString = substr($y, 1);
					}
					if (is_numeric($offsetString)) {
						$y = $sender->getPosition()->y + ($negative ? -floatval($offsetString) : floatval($offsetString));
					}
				}
				if (str_starts_with($z, "~")) {
					$offsetString = substr($z, 1);
					$negative = str_starts_with($offsetString, "-") || !str_starts_with($offsetString, "+");
					if ($negative) {
						$offsetString = substr($z, 1);
					}
					if (is_numeric($offsetString)) {
						$z = $sender->getPosition()->z + ($negative ? -floatval($offsetString) : floatval($offsetString));
					}
				}
			}
			foreach ([$x, $y, $z] as $coordinate) {
				if (!is_numeric($coordinate) && $coordinate[0] != "~") {
					$this->sendSyntaxError($sender, $coordinate, implode(" ", $args), $coordinate);
					return null;
				}
			}
			return new Vector3(floatval($x), floatval($y), floatval($z));
		}
		return null;
	}

	static function groupParameter(bool $optional): CommandParameter{
		return CommandParameter::enum("group", new CommandEnum("group", array_keys(Cache::getInstance()->groupNames)), self::FLAG_AQUA, $optional);
	}

	static function onlinePlayerParameter(bool $optional = true): CommandParameter{
		return CommandParameter::enum("target", new CommandEnum("onlinePlayers", Cache::getInstance()->getOnlinePlayers()), self::FLAG_AQUA, $optional);
	}

	static function registeredPlayerParameter(bool $optional = true): CommandParameter{
		return CommandParameter::enum("target", new CommandEnum("registeredPlayers", array_keys(Cache::getInstance()->registeredPlayers)), self::FLAG_AQUA, $optional);
	}

	/**
	 * Function isConsoleCommand
	 * @return bool
	 */
	public function isConsoleCommand(): bool{
		return $this->isConsoleCommand;
	}

	/**
	 * Function isPlayerCommand
	 * @return bool
	 */
	public function isPlayerCommand(): bool{
		return $this->isPlayerCommand;
	}
}
