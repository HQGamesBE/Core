<?php
declare(strict_types=1);
namespace HQGames\Core\forms\elements;
use pocketmine\form\FormValidationException;


/**
 * Class Dropdown
 * @package HQGames\Core\forms\elements
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:44
 * @ide PhpStorm
 * @project Core
 */
class Dropdown extends Element{
	/** @var string[] */
	protected array $options;
	protected int $default;

	/**
	 * @param string $text
	 * @param string[] $options
	 * @param int $default
	 */
	public function __construct(string $text, array $options, int $default = 0){
		parent::__construct($text);
		$this->options = $options;
		$this->default = $default;
	}

	/**
	 * @return array
	 */
	public function getOptions(): array{
		return $this->options;
	}

	/**
	 * @return string
	 */
	public function getSelectedOption(): string{
		return $this->options[$this->value];
	}

	/**
	 * @return int
	 */
	public function getDefault(): int{
		return $this->default;
	}

	/**
	 * @return string
	 */
	public function getType(): string{
		return "dropdown";
	}

	/**
	 * @return array
	 */
	public function serializeElementData(): array{
		return [
			"options" => $this->options,
			"default" => $this->default,
		];
	}

	public function validate(mixed $value): void{
		parent::validate($value);
		if (!isset($this->options[$value])) {
			throw new FormValidationException("Option with index $value does not exist in dropdown");
		}
	}
}