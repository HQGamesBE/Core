# Commando

### Check if addon is loaded
```php
if (!\HQGames\addons\AddonManager::isRegistered(\HQGames\addons\commando\Commando::class)) {
	\HQGames\addons\AddonManager::getInstance()->registerAddon(\HQGames\addons\commando\Commando::class);
}
```

### Register a enum

```php
use \HQGames\addons\commando\SoftEnumCache;

SoftEnumCache::addEnum(/*CommandEnum*/$enum);
SoftEnumCache::updateEnum(/*string*/$enum_name, /*string[]*/$enum_data);
SoftEnumCache::removeEnum(/*string*/$enum_name);
```

<sub><a href="https://github.com/CortexPE">CortexPE</a> / <a href="https://github.com/CortexPE/Commando">
Commando</a></sub>