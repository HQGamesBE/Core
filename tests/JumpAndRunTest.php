<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

include dirname(__DIR__) . "/vendor/autoload.php";
$current_block = \pocketmine\world\Position::zero();
$next_block = \pocketmine\world\Position::zero();

function chooseNextBlock(): string{
	return \pocketmine\utils\Terminal::$COLOR_LIGHT_PURPLE . "[Object: Block]" . \pocketmine\utils\Terminal::$FORMAT_RESET;
}
echo 'Before($current_block): ' . $current_block->__toString() . PHP_EOL;
echo 'Before($next_block): ' . $next_block->__toString() . PHP_EOL;
echo 'After($current_block): ' . calculateNextBlock() . PHP_EOL;
echo 'After($next_block): ' . $next_block->__toString() . PHP_EOL;

function calculateNextBlock(): string{
	global $next_block, $current_block;
	/** @SEE: $this->current_block = $this->next_block */
	$pos = $current_block;
	$x = $y = $z = 0;
	$left = 4;
	generateCoordinate: {
		if ($left > 0) {
			switch (mt_rand(1, 3)) {
				case 1:
				{ // X coordinate
					if ($x >= 3)
						goto generateCoordinate;
					if (mt_rand(0, 1) == 1)	$x--;
					else $x++;
					break;
				}
				case 2:
				{ // Y coordinate
					if ($y >= 1)
						goto generateCoordinate;
					$y++;
					break;
				}
				case 3:
				{ // Z coordinate
					if ($z >= 3)
						goto generateCoordinate;
					if (mt_rand(0, 1) == 1) $z--;
					else	$z++;
					break;
				}
			}
			$left--;
			goto generateCoordinate;
		}
	}
	if ($y >= 1 && ($x > 3 || $z > 3)) {
		echo "[ Recursion ]" . PHP_EOL;
		return calculateNextBlock(); // NOTE: pre-handle unreachable jumps
	}
	return $pos;
}