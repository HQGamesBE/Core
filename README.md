# Core

<a href='https://ko-fi.com/xx_arox' target='_blank'><img height='36' style='border:0px;height:36px;' src='https://cdn.ko-fi.com/cdn/kofi3.png?v=3' border='0' alt='Buy Me a Coffee' /></a>

<details>
<summary>Fake Blocks</summary>

```php
/**
* @var \pocketmine\player\Player $player
* @var \pocketmine\world\Position $position
 */
$fakeblock = FakeBlockManager::getInstance()->create(\pocketmine\block\VanillaBlocks::GLASS(), $position, null); // NOTE: null = all users. 
$fakeblock->addViewer($player);
$fakeblock->removeViewer($player);
$fakeblock->getViewers();
FakeBlockManager::getInstance()->destroy($fakeblock);
```
</details>