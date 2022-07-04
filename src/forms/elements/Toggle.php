<?php
declare(strict_types=1);
namespace HQGames\Core\forms\elements;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use pocketmine\form\FormValidationException;


/**
 * Class Toggle
 * @package HQGames\Core\forms\elements
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:51
 * @ide PhpStorm
 * @project Core
 */
class Toggle extends Element{
	protected bool $default;

	/**
	 * Toggle constructor.
	 * @param string $text
	 * @param bool $default
	 */
	#[Pure] public function __construct(string $text, bool $default = false){
		parent::__construct($text);
		$this->default = $default;
	}

	/**
	 * Function getValue
	 * @return bool
	 */
	public function getValue(): bool{
		return parent::getValue();
	}

	/**
	 * Function hasChanged
	 * @return bool
	 */
	public function hasChanged(): bool{
		return $this->default !== $this->value;
	}

	/**
	 * Function getDefault
	 * @return bool
	 */
	public function getDefault(): bool{
		return $this->default;
	}

	/**
	 * Function getType
	 * @return string
	 */
	public function getType(): string{
		return "toggle";
	}

	/**
	 * Function serializeElementData
	 * @return bool[]
	 */
	#[ArrayShape(["default" => "bool"])] public function serializeElementData(): array{
		return [
			"default" => $this->default,
		];
	}

	/**
	 * Function validate
	 * @param mixed $value
	 * @return void
	 */
	public function validate(mixed $value): void{
		if (!is_bool($value))
			throw new FormValidationException("Expected bool, got " . gettype($value));
	}
}