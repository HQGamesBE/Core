<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\Core\addons;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use PHPUnit\TextUI\ReflectionException;
use pocketmine\command\Command;
use pocketmine\event\HandlerListManager;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginDescriptionParseException;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\Internet;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Terminal;
use PrefixedLogger;
use ReflectionClass;


/**
 * Class Addon
 * @package HQGames\Core\addons
 * @author Jan Sohn / xxAROX
 * @date 22. July, 2022 - 05:01
 * @ide PhpStorm
 * @project Core
 */
abstract class Addon{
	private PluginBase $registrant;
	private PrefixedLogger $logger;
	private string $configFile = "";
	private ?Config $config = null;
	private bool $isRegistered = false;
	/** @var Command[] */
	private array $commands = [];
	/** @var Listener[] */
	private array $listeners = [];

	/**
	 * Function register
	 * @param PluginBase $registrant
	 * @return void
	 */
	final function register(PluginBase $registrant): void{
		if ($this->isRegistered) throw new InvalidArgumentException("Addon '{$this->getName()}' is already registered");
		static::setInstance($this);
		$this->registrant = $registrant;
		$this->configFile = $this->getDataFolder() . "config.json";
		$this->logger = new PrefixedLogger(AddonManager::getInstance()->getLogger(), Terminal::$COLOR_AQUA . static::getName() . Terminal::$FORMAT_RESET);
		$this->isRegistered = true;
	}

	/**
	 * Function unregister
	 * @return void
	 */
	function unregister(): void{
		if (!$this->isRegistered) throw new InvalidArgumentException("Addon '{$this->getName()}' is not registered");
		$this->isRegistered = false;
		foreach ($this->listeners as $listener) $this->unregisterListener($listener);
		foreach ($this->commands as $command) $this->unregisterCommand($command);
		self::destroy();
	}

	/**
	 * Function registerCommand
	 * @param Command $command
	 * @return void
	 */
	public final function registerCommand(Command $command): void{
		if (isset($this->commands[mb_strtolower($command->getName())])) throw new InvalidArgumentException("Command '{$command->getName()}' is already registered");
		$this->commands[mb_strtolower($command->getName())] = $command;
		$this->getServer()->getCommandMap()->register(mb_strtolower(static::getName()), $command);
	}

	/**
	 * Function unregisterCommand
	 * @param Command|string|class-string $command
	 * @return void
	 */
	public final function unregisterCommand(Command|string $command): void{
		if (!isset($this->commands[mb_strtolower(($command instanceof Command ? $command->getName() : $command))])) throw new InvalidArgumentException("Command '{$command->getName()}' is not registered");
		unset($this->commands[mb_strtolower(($command instanceof Command ? $command->getName() : $command))]);
		$this->getServer()->getCommandMap()->unregister(($command instanceof Command ? $command : $this->getServer()->getCommandMap()->getCommand($command)));
	}

	/**
	 * Function registerCommand
	 * @param Listener $listener
	 * @return void
	 */
	public final function registerListener(Listener $listener): void{
		if (isset($this->listeners[$listener::class])) throw new InvalidArgumentException("Listener '" . $listener::class . "' is already registered");
		$this->listeners[$listener::class] = $listener;
		$this->getServer()->getPluginManager()->registerEvents($listener, $this->registrant);
	}

	/**
	 * Function unregisterListener
	 * @param Listener|class-string $listener
	 * @return void
	 */
	public final function unregisterListener(Listener|string $listener): void{
		if (!isset($this->listeners[$listener instanceof Listener ? $listener::class : $listener])) throw new InvalidArgumentException("Listener '" . $listener::class . "' is not registered");
		unset($this->listeners[$listener instanceof Listener ? $listener::class : $listener]);
		HandlerListManager::global()->unregisterAll($listener);
	}

	/**
	 * Function isRegistered
	 * @return bool
	 */
	public final function isRegistered(): bool{
		return $this->isRegistered;
	}

	/**
	 * Function getName
	 * @return string
	 */
	public static function getName(): string{
		return (new ReflectionClass(static::class))->getShortName();
	}

	/**
	 * Function getVersion
	 * @return string
	 */
	abstract public static function getVersion(): string;

	/**
	 * Function getAuthors
	 * @return string[]
	 */
	abstract public static function getAuthors(): array;

	/**
	 * Function getLink
	 * @return null|string
	 */
	public static function getLink(): ?string{
		return null;
	}

	/**
	 * Function checkForUpdate
	 * @return void
	 */
	public static final function checkForUpdate(): void{
		if (is_null(static::getLink())) return;
		static::getInstance()->getServer()->getAsyncPool()->submitTask(new class(static::class) extends AsyncTask{
			private string $url;
			public function __construct(protected Addon|string $class){
				if (!preg_match("/^https?:\/\/.+/", $class::getLink())) throw new InvalidArgumentException("Invalid URL");
				if (!str_ends_with($class::getLink(), ".yml")) throw new InvalidArgumentException("Invalid URL");
				$this->url = $class::getLink();
				$this->class::getInstance()->getLogger()->debug("Checking for updates...");
			}
			public function onRun(): void{
				$result = Internet::getURL($this->url);
				if (is_null($result) || $result->getCode() !== 200){
					$this->setResult("Couldn't check for updates");
					return;
				}
				$description = yaml_parse($result->getBody());
				if ($description === false) throw new PluginDescriptionParseException("YAML parsing error in plugin manifest");
				if (!is_array($description)) throw new PluginDescriptionParseException("Invalid structure of plugin manifest, expected array but have " . get_debug_type($description));
				if (!isset($description["version"])) throw new PluginDescriptionParseException("Invalid structure of plugin manifest, expected 'version' key");
				$currentVersion = $this->class::getVersion();
				$newVersion = $description["version"];
				if (version_compare($currentVersion, $newVersion, "<")) {
					$this->class::getInstance()->getLogger()->info("New version available: " . $newVersion);
					$normalLink = "https://github.com/" . $this->class::getAuthors()[0] . "/" . $this->class::getName();
					$branch = explode("/", $this->class::getLink())[5];
					$this->setResult($normalLink . "/tree/" . $branch);
				} else
					$this->setResult(null);
			}

			public function onCompletion(): void{
				if (is_null(null)) return;
				else if (str_starts_with(($result=$this->getResult()), "https://")) $this->class::getInstance()->getLogger()->info("Update available: " . $this->getResult());
				else $this->class::getInstance()->getLogger()->error($result);
			}
		});
	}

	/**
	 * Function getVersion
	 * @param string $from_version
	 * @param string $to_version
	 * @return array
	 */
	#[ArrayShape([
		"@removed" => "string[]",
		"@edits" => "array<string, mixed>",
	])] public function getVersionUpdate(string $from_version, string $to_version): array{
		return [];
	}

	/**
	 * Function doVersionUpdate
	 * @return void
	 * @internal
	 */
	public final function doVersionUpdate(): void{
		$config = $this->getConfig();
		$from_version = $config->get("version", "0.0.0");
		$to_version = static::getVersion();


		if ($from_version === $to_version) return;
		else if (version_compare($from_version, $to_version, ">")) {
			throw new InvalidArgumentException("Version '{$from_version}' is newer than '{$to_version}'");
		} else {
			$update_obj = $this->getVersionUpdate($from_version, $to_version);
			$this->logger->warning("Version '{$from_version}' is outdated and will be updated to '{$to_version}'");
			if (isset($update_obj["@removed"]) && count($update_obj["@removed"]) > 0)
				$this->logger->warning("        Removed: " . implode(", ", $update_obj["@removed"]));
			if (isset($update_obj["@edits"]) && count($update_obj["@edits"]) > 0)
				$this->logger->warning("        Edits: " . implode(", ", array_keys($update_obj["@edits"])));
			$config->set("enabled", true);
			$config->set("version", $to_version);
			if ($config->hasChanged()) $config->save();
		}
	}

	/**
	 * Function getName
	 * @return array
	 * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
	 */
	protected function getDefaultConfig(): array{
		return [
			"enabled" => true,
			"version" => static::getVersion(),
		];
	}

	/**
	 * Function onEnable
	 * @return void
	 */
	public function onEnable(): void{
	}

	/**
	 * Function onDisable
	 * @return void
	 */
	public function onDisable(): void{
	}

	/**
	 * Function getConfig
	 * @return Config
	 */
	public final function getConfig(): Config{
		if (is_null($this->config)) $this->reloadConfig();
		return $this->config;
	}

	/**
	 * Function saveConfig
	 * @return void
	 * @throws \JsonException if fails to save the config data
	 */
	public final function saveConfig(): void{
		$this->getConfig()->save();
	}

	public final function reloadConfig(): void{
		unset($this->config);
		$this->config = new Config($this->configFile, Config::JSON, $this->getDefaultConfig());
	}

	/**
	 * Function getLogger
	 * @return PrefixedLogger
	 */
	public final function getLogger(): PrefixedLogger{
		return $this->logger;
	}

	/**
	 * Function getRegistrant
	 * @return PluginBase
	 */
	public final function getRegistrant(): PluginBase{
		return $this->registrant;
	}

	/**
	 * Function getServer
	 * @return Server
	 */
	#[Pure] public final function getServer(): Server{
		return $this->registrant->getServer();
	}

	/**
	 * Function getScheduler
	 * @return TaskScheduler
	 */
	#[Pure] public final function getScheduler(): TaskScheduler{
		return $this->registrant->getScheduler();
	}

	/**
	 * Function getDataFolder
	 * @return string
	 */
	public final function getDataFolder(): string{
		$path = $this->getServer()->getDataPath() . "addons/" . static::getName() . "/";
		if (!is_dir($path)) @mkdir($path, 0777, true);
		return $path;
	}

	/**
	 * Function getCommands
	 * @return Command[]
	 */
	public final function getCommands(): array{
		return $this->commands;
	}

	/**
	 * Function getListeners
	 * @return Listener[]
	 */
	public final function getListeners(): array{
		return $this->listeners;
	}
}
