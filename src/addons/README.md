# Addons

### Example

```php
class ExamplePlugin extends \pocketmine\plugin\PluginBase{
	public function onEnable(): void{
		$this->registerAddons();
	}
	public function onDisable(): void{
		AddonManager::getInstance()->unregisterAll();
	}
	private function registerAddons(): void{
		new \HQGames\addons\AddonManager($this);
		$this->getLogger()->info("Registering addons...");
		$addons = [
			ExampleAddon::class,
		];
		foreach ($addons as $addon) {
			if (!class_exists($addon)) {
				$this->getLogger()->error("Addon '{$addon}' is not found!");
				continue;
			}
			AddonManager::getInstance()->registerAddon($addon);
			$this->getLogger()->debug("Addon '{$addon}' is registered!");
		}
	}
}

class ExampleAddon extends \HQGames\addons\Addon{
	public function onEnable(): void{
		$this->getLogger()->info("Example addon is enabled!");
		/** @param \pocketmine\command\Command $command */
		$this->registerCommand($command);
		/** @param \pocketmine\event\Listener $listener */
		$this->registerListener($listener);
	}
	public function onDisable(): void{
		$this->getLogger()->info("Example addon is disabled!");
	}
	protected function getDefaultConfig() : array{
		$default = parent::getDefaultConfig();
		$default["example"] = "This is the default example text";
		return $default;
	}
	
	public static function getVersion() : string{
 		return "1.0.0";
	}
	
	public static function getAuthors() : array{
 		return ["xxAROX"]
	}
	
	public function getVersionUpdate(string $from_version) : array{
 		$obj = parent::getVersionUpdate($from_version,$to_version);
 		
 		if (version_compare($from_version, "0.0.2","<")) {
 			$obj["@edits"]["example"] = "This is the example text";
 		}
 		if (version_compare($from_version, "0.5.0","<")) {
 			$obj["@removed"] = ["example"];
 			$obj["@edits"] = [
 				"changed_example" => "success" // changed_example is potential new not edited, because it will remove first
 			];
 		} 		
 		return $obj
	}

}
```

