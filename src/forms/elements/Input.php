<?php
declare(strict_types=1);
namespace HQGames\Core\forms\elements;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use pocketmine\form\FormValidationException;


/**
 * Class Input
 * @package HQGames\Core\forms\elements
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:44
 * @ide PhpStorm
 * @project Core
 */
class Input extends Element{
	protected string $placeholder;
	protected string $default;

	/**
	 * Input constructor.
	 * @param string $text
	 * @param string $placeholder
	 * @param string $default
	 */
	#[Pure] public function __construct(string $text, string $placeholder = "", string $default = ""){
		parent::__construct($text);
		$this->placeholder = $placeholder;
		$this->default = $default;
	}

	/**
	 * Function getValue
	 * @return string
	 */
	public function getValue(): string{
		return parent::getValue();
	}

	/**
	 * Function getPlaceholder
	 * @return string
	 */
	public function getPlaceholder(): string{
		return $this->placeholder;
	}

	/**
	 * Function getDefault
	 * @return string
	 */
	public function getDefault(): string{
		return $this->default;
	}

	/**
	 * Function getType
	 * @return string
	 */
	public function getType(): string{
		return "input";
	}

	/**
	 * Function serializeElementData
	 * @return array
	 */
	#[ArrayShape(["placeholder" => "string", "default" => "string"])] public function serializeElementData(): array{
		return [
			"placeholder" => $this->placeholder,
			"default"     => $this->default,
		];
	}

	/**
	 * Function validate
	 * @param mixed $value
	 * @return void
	 */
	public function validate(mixed $value): void{
		if (!is_string($value)) {
			throw new FormValidationException("Expected string, got " . gettype($value));
		}
	}
}