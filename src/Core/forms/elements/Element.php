<?php
declare(strict_types=1);
namespace HQGames\Core\forms\elements;
use JsonSerializable;
use pocketmine\form\FormValidationException;


/**
 * Class Element
 * @package HQGames\Core\forms\elements
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:43
 * @ide PhpStorm
 * @project Core
 */
abstract class Element implements JsonSerializable{
	protected string $text;
	protected mixed $value;

	/**
	 * Element constructor.
	 * @param string $text
	 */
	public function __construct(string $text){
		$this->text = $text;
	}

	/**
	 * Function getValue
	 * @return mixed
	 */
	public function getValue(): mixed{
		return $this->value;
	}

	/**
	 * Function setValue
	 * @param mixed $value
	 * @return void
	 */
	public function setValue(mixed $value){
		$this->value = $value;
	}

	/**
	 * Function jsonSerialize
	 * @return array
	 */
	public function jsonSerialize(): array{
		$array = ["text" => $this->getText()];
		if ($this->getType() !== null) {
			$array["type"] = $this->getType();
		}
		return $array + $this->serializeElementData();
	}

	/**
	 * Function getText
	 * @return string
	 */
	public function getText(): string{
		return $this->text;
	}

	/**
	 * Function getType
	 * @return null|string
	 */
	abstract public function getType(): ?string;

	/**
	 * Function serializeElementData
	 * @return array
	 */
	abstract public function serializeElementData(): array;

	/**
	 * Function validate
	 * @param mixed $value
	 * @return void
	 */
	public function validate(mixed $value): void{
		if (!is_int($value)) {
			throw new FormValidationException("Expected int, got " . gettype($value));
		}
	}
}