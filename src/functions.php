<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
namespace HQGames;
use InvalidArgumentException;
use pocketmine\entity\Location;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\Position;


/**
 * Function decodeVector2
 * @param string $vector2
 * @param bool $asFloat
 * @return Vector2
 */
function decodeVector2(string $vector2, bool $asFloat = false): Vector2{
	$ex = explode(":", $vector2);
	if (count($ex) !== 2)
		throw new InvalidArgumentException("Invalid format: $vector2");
	return new Vector2($asFloat ? floatval($ex[0]) : intval($ex[0]), $asFloat ? floatval($ex[1]) : intval($ex[1]));
}

/**
 * Function encodeVector2
 * @param Vector2 $vector2
 * @return string
 */
function encodeVector2(Vector2 $vector2): string{
	return $vector2->x . ":" . $vector2->y;
}

/**
 * Function decodeVector3
 * @param string $vector3
 * @param bool $asFloat
 * @return Vector3
 */
function decodeVector3(string $vector3, bool $asFloat = false): Vector3{
	$ex = explode(":", $vector3);
	if (count($ex) !== 3)
		throw new InvalidArgumentException("Invalid format: $vector3");
	return new Vector3($asFloat ? floatval($ex[0]) : intval($ex[0]), $asFloat ? floatval($ex[1])
		: intval($ex[1]), $asFloat ? floatval($ex[2]) : intval($ex[2]));
}

/**
 * Function encodeVector3
 * @param Vector3 $vector3
 * @return string
 */
function encodeVector3(Vector3 $vector3): string{
	return $vector3->x . ":" . $vector3->y . ":" . $vector3->z;
}

/**
 * Function decodePosition
 * @param string $position
 * @param bool $asFloat
 * @return Position
 */
function decodePosition(string $position, bool $asFloat = false): Position{
	$ex = explode(":", $position);
	if (count($ex) !== 4)
		throw new InvalidArgumentException("Invalid format: $position");
	if (!Server::getInstance()->getWorldManager()->loadWorld($ex[3]))
		throw new InvalidArgumentException("Invalid world: $ex[3]");
	$world = Server::getInstance()->getWorldManager()->getWorldByName($ex[3]);
	return new Position($asFloat ? floatval($ex[0]) : intval($ex[0]), $asFloat ? floatval($ex[1])
		: intval($ex[1]), $asFloat ? floatval($ex[2]) : intval($ex[2]), $world);
}

/**
 * Function encodePosition
 * @param Position $position
 * @return string
 */
function encodePosition(Position $position): string{
	return $position->x . ":" . $position->y . ":" . $position->z . ":" . $position->getWorld()->getFolderName();
}

/**
 * Function decodeLocation
 * @param string $location
 * @param bool $asFloat
 * @return Location
 */
function decodeLocation(string $location, bool $asFloat = false): Location{
	$ex = explode(":", $location);
	if (count($ex) !== 6)
		throw new InvalidArgumentException("Invalid format: $location");
	if (!Server::getInstance()->getWorldManager()->loadWorld($ex[3]))
		throw new InvalidArgumentException("Invalid world: $ex[3]");
	$world = Server::getInstance()->getWorldManager()->getWorldByName($ex[3]);
	return new Location($asFloat ? floatval($ex[0]) : intval($ex[0]), $asFloat ? floatval($ex[1])
		: intval($ex[1]), $asFloat ? floatval($ex[2]) : intval($ex[2]), $world, floatval($ex[4]), floatval($ex[5]));
}

/**
 * Function encodeLocation
 * @param Location $location
 * @return string
 */
function encodeLocation(Location $location): string{
	return $location->x . ":" . $location->y . ":" . $location->z . ":" . $location->getWorld()->getFolderName() . ":" . $location->yaw . ":" . $location->pitch;
}