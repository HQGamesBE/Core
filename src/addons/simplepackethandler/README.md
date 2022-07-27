# SimplePacketHandler
by [Muqsit](https://github.com/Muqsit)


### Check if addon is loaded
```php
if (!AddonManager::isRegistered(SimplePacketHandler::class)) {
	AddonManager::getInstance()->register(SimplePacketHandler::class);
}
```

### Create interceptor
```php
/**
 * @var \HQGames\addons\simplepackethandler\interceptor\PacketInterceptor $interceptor
 */
$interceptor = SimplePacketHandler::getInstance()->createInterceptor(EventPriority::NORMAL, false);

$interceptor->interceptIncoming($in=function (\pocketmine\network\mcpe\protocol\AvailableCommandsPacket $packet, \pocketmine\network\mcpe\NetworkSession $target): bool{
	echo "Got AvailableCommandsPacket" . PHP_EOL;
	return false; // cancel packet receive event
});
$interceptor->interceptOutgoing($out=function (\pocketmine\network\mcpe\protocol\LoginPacket $packet, \pocketmine\network\mcpe\NetworkSession $target): bool{
	echo "LoginPacket will sent" . PHP_EOL;
	return false; // cancel packet send event
});
```

### Remove interceptor
```php
/**
 * @var \HQGames\addons\simplepackethandler\interceptor\PacketInterceptor $interceptor
 * @var Closure $in
 * @var Closure $out
 */
$interceptor->unregisterIncomingInterceptor($in);
$interceptor->unregisterOutgoingInterceptor($out);
```

### Create Monitor
```php
$monitor = \HQGames\addons\simplepackethandler\SimplePacketHandler::getInstance()->createMonitor(EventPriority::NORMAL, false);
$monitor->monitorIncoming($in=function (\pocketmine\network\mcpe\protocol\AvailableCommandsPacket $packet, \pocketmine\network\mcpe\NetworkSession $target): void{
	echo "Got AvailableCommandsPacket" . PHP_EOL;
});
$monitor->monitorOutgoing($out=function (\pocketmine\network\mcpe\protocol\LoginPacket $packet, \pocketmine\network\mcpe\NetworkSession $target): void{
	echo "LoginPacket will sent" . PHP_EOL;
});
```

### Remove Monitor
```php
/**
 * @var \HQGames\addons\simplepackethandler\monitor\PacketMonitor $monitor
 * @var Closure $in
 * @var Closure $out
 */
$monitor->unregisterIncomingMonitor($in);
$monitor->unregisterOutgoingMonitor($out);
```