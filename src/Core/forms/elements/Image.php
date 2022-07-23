<?php
declare(strict_types=1);
namespace HQGames\Core\forms\elements;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;


/**
 * Class Image
 * @package HQGames\Core\forms\elements
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:44
 * @ide PhpStorm
 * @project Core
 */
class Image implements JsonSerializable{
	public const TYPE_URL  = "url";
	public const TYPE_PATH = "path";
	private string $type;
	private string $data;

	/**
	 * Image constructor.
	 * @param string $data
	 * @param string $type
	 */
	public function __construct(string $data, string $type = self::TYPE_URL){
		$this->type = $type;
		$this->data = $data;
	}

	/**
	 * Function getType
	 * @return string
	 */
	public function getType(): string{
		return $this->type;
	}

	/**
	 * Function getData
	 * @return string
	 */
	public function getData(): string{
		return $this->data;
	}

	/**
	 * Function jsonSerialize
	 * @return array
	 */
	#[ArrayShape(["type" => "string", "data" => "string"])] public function jsonSerialize(): array{
		return [
			"type" => $this->type,
			"data" => $this->data,
		];
	}
}