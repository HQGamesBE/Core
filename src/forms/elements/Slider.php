<?php
declare(strict_types=1);
namespace HQGames\Core\forms\elements;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use pocketmine\form\FormValidationException;


/**
 * Class Slider
 * @package HQGames\Core\forms\elements
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:50
 * @ide PhpStorm
 * @project Core
 */
class Slider extends Element{
	protected float $min;
	protected float $max;
	protected float $step = 1.0;
	protected float $default;

	/**
	 * Slider constructor.
	 * @param string $text
	 * @param float $min
	 * @param float $max
	 * @param float $step
	 * @param null|float $default
	 */
	public function __construct(string $text, float $min, float $max, float $step = 1.0, ?float $default = null){
		parent::__construct($text);
		if ($this->min > $this->max)
			throw new InvalidArgumentException("Slider min value should be less than max value");
		$this->min = $min;
		$this->max = $max;
		if ($default !== null) {
			if ($default > $this->max or $default < $this->min)
				throw new InvalidArgumentException("Default must be in range $this->min ... $this->max");
			$this->default = $default;
		} else {
			$this->default = $this->min;
		}
		if ($step <= 0)
			throw new InvalidArgumentException("Step must be greater than zero");
		$this->step = $step;
	}

	/**
	 * Function getValue
	 * @return float|int
	 */
	public function getValue(): float|int{
		return parent::getValue();
	}

	/**
	 * Function getMin
	 * @return float
	 */
	public function getMin(): float{
		return $this->min;
	}

	/**
	 * Function getMax
	 * @return float
	 */
	public function getMax(): float{
		return $this->max;
	}

	/**
	 * Function getStep
	 * @return float
	 */
	public function getStep(): float{
		return $this->step;
	}

	/**
	 * Function getDefault
	 * @return float
	 */
	public function getDefault(): float{
		return $this->default;
	}

	/**
	 * Function getType
	 * @return string
	 */
	public function getType(): string{
		return "slider";
	}

	/**
	 * Function serializeElementData
	 * @return array
	 */
	#[ArrayShape(["min"     => "float",
				  "max"     => "float",
				  "default" => "float",
				  "step"    => "float",
	])] public function serializeElementData(): array{
		return [
			"min"     => $this->min,
			"max"     => $this->max,
			"default" => $this->default,
			"step"    => $this->step,
		];
	}

	public function validate(mixed $value): void{
		if (!is_int($value) && !is_float($value))
			throw new FormValidationException("Expected int or float, got " . gettype($value));
	}
}