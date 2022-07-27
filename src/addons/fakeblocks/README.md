# Fake Blocks
by [Ivan]

### Check if addon is loaded
```php
if (!\HQGames\addons\AddonManager::isRegistered(\HQGames\addons\fakeblocks\FakeBlockManager::class)) {
	\HQGames\addons\AddonManager::getInstance()->registerAddon(\HQGames\addons\fakeblocks\FakeBlockManager::class);
}
```

### Create a fake block, that everyone can see
```php
/**
 * @param \pocketmine\world\Position $position
 */
$fake_block_for_everyone = \HQGames\addons\fakeblocks\FakeBlockManager::getInstance()->createFakeBlock(
	\pocketmine\block\VanillaBlocks::STAINED_GLASS()->setColor(\pocketmine\block\utils\DyeColor::YELLOW()),
	$position,
	null
); // INFO: returns FakeBlock object
```

### Create a fake block, that only specified players can see
```php
/**
 * @param \pocketmine\world\Position $position
* @param \pocketmine\Player $specified_player1
* @param \pocketmine\Player $specified_player2
* @param \pocketmine\Player $specified_player3
 */
$fake_block_for_specified_players = \HQGames\addons\fakeblocks\FakeBlockManager::getInstance()->createFakeBlock(
	\pocketmine\block\VanillaBlocks::STAINED_GLASS()->setColor(\pocketmine\block\utils\DyeColor::YELLOW()),
	$position,
	[$specified_player1, $specified_player2, $specified_player3]
); // INFO: returns FakeBlock object
```

### Get all fake blocks on a position
```php
/**
 * @param \pocketmine\world\Position $position
 * @param \pocketmine\world\World $world
 * @param int $chunkX
 * @param int $chunkY
 */
$fakeblocks_at_position = \HQGames\addons\fakeblocks\FakeBlockManager::getInstance()->getFakeBlocks($position); // INFO: returns FakeBlock[]
$fakeblocks_at_chunk = \HQGames\addons\fakeblocks\FakeBlockManager::getInstance()->getFakeBlocksAt($world, $chunkX, $chunkY); // INFO: returns FakeBlock[]
```

### Destroy a fake block
```php
/**
* @param \HQGames\addons\fakeblocks\FakeBlock $fakeblock
 */
\HQGames\addons\fakeblocks\FakeBlockManager::getInstance()->destroyFakeBlock($fakeblock);
```
