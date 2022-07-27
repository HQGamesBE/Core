<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\addons;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\RegistryTrait;
use pocketmine\utils\SingletonTrait;
use PrefixedLogger;

use function Sodium\add;


/**
 * Class AddonManager
 * @package HQGames\addons
 * @author Jan Sohn / xxAROX
 * @date 22. July, 2022 - 05:23
 * @ide PhpStorm
 * @project Core
 */
final class AddonManager{
	use SingletonTrait{
		setInstance as private static;
		reset as private;
	}
	private PrefixedLogger $logger;
	private PrefixedLogger $addon_logger;
	/** @var Addon[] */
	private array $addons = [];


	/**
	 * AddonManager constructor.
	 * @param PluginBase $plugin
	 */
	public function __construct(private PluginBase $plugin){
		$this->logger = new PrefixedLogger($plugin->getLogger(), "AddonManager");
		$this->addon_logger = new PrefixedLogger($plugin->getLogger(), "Addon");
		$this->setInstance($this);
	}

	/**
	 * Function isRegistered
	 * @param Addon|class-string $addon_class
	 * @return bool
	 */
	public static function isRegistered(Addon|string $addon_class): bool{
		return isset(self::getInstance()->addons[mb_strtolower($addon_class::getName())]);
	}

	/**
	 * Function registerAddon
	 * @param class-string $addon
	 * @return void
	 */
	public function registerAddon(string $addon): void{
		if (!class_exists($addon) || !is_a($addon, Addon::class, true)) throw new InvalidArgumentException("Class '$addon' is not a valid addon class");
		if (self::isRegistered($addon) || isset($this->addons[mb_strtolower($addon::getName())])) return;

		/** @var Addon $addon */
		$addon = new $addon;
		$this->addons[mb_strtolower($addon::getName())] = $addon;
		$this->logger->debug("Registering {$addon::getName()} addon");
		$addon->register($this->plugin);
		if ($addon->getConfig()->get("enabled", true) == false) {
			$this->plugin->getLogger()->info("§oAddon '{$addon::getName()}' is disabled");
			$addon->unregister();
			unset($this->addons[mb_strtolower($addon::getName())]);
			return;
		}
		$addon::checkForUpdate();
		$this->logger->info("Enabling {$addon::getName()} addon");
		$addon->onEnable();
	}

	/**
	 * Function unregisterAddon
	 * @param Addon|class-string $addon
	 * @return void
	 */
	public function unregisterAddon(Addon|string $addon): void{
		if (class_exists($addon)) {
			if (!is_a($addon, Addon::class))
				throw new InvalidArgumentException("{$addon} must be an instance of " . Addon::class);
			$addonName = $addon::getName();
			if (is_string($addon)) $addon = $this->addons[mb_strtolower($addon)] ?? throw new InvalidArgumentException("Addon '{$addonName}' is not registered");
			if (!isset($this->addons[mb_strtolower($addonName)])) throw new InvalidArgumentException("Addon '{$addonName}' is not registered");
			unset($this->addons[mb_strtolower($addonName)]);
			$this->logger->info("Disabling {$addonName} addon");
			$addon->onDisable();
			foreach ($addon->getListeners() as $listener) $addon->unregisterListener($listener);
			foreach ($addon->getCommands() as $command) $addon->unregisterCommand($command);
		}
	}

	/**
	 * Function unregisterAll
	 * @return void
	 */
	public function unregisterAll(): void{
		foreach ($this->addons as $addon) {
			$addon->onDisable();
			foreach ($addon->getCommands() as $command) $addon->unregisterCommand($command);
			foreach ($addon->getListeners() as $command) $addon->unregisterListener($command);
		}
		unset($this->addons);
		$this->addons = [];
	}

	/**
	 * Function getAddons
	 * @return Addon[]
	 */
	public function getAddons(): array{
		return $this->addons;
	}

	/**
	 * Function getLogger
	 * @return PrefixedLogger
	 */
	public function getLogger(): PrefixedLogger{
		return $this->logger;
	}

	/**
	 * Function getAddonLogger
	 * @return PrefixedLogger
	 */
	public function getAddonLogger(): PrefixedLogger{
		return $this->addon_logger;
	}

	/**
	 * Function getPlugin
	 * @return PluginBase
	 */
	public function getPlugin(): PluginBase{
		return $this->plugin;
	}
}
