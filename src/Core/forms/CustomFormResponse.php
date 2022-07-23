<?php
declare(strict_types=1);
namespace HQGames\Core\forms;
use HQGames\Core\forms\elements\{Dropdown, Element, Input, Label, Slider, StepSlider, Toggle};
use pocketmine\form\FormValidationException;


/**
 * Class CustomFormResponse
 * @package HQGames\Core\forms
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:54
 * @ide PhpStorm
 * @project Core
 */
class CustomFormResponse{
	/** @var Element[] */
	private array $elements;

	/**
	 * CustomFormResponse constructor.
	 * @param array $elements
	 */
	public function __construct(array $elements){
		$this->elements = $elements;
	}

	/**
	 * Function getDropdown
	 * @return Dropdown
	 */
	public function getDropdown(): Dropdown{
		return $this->tryGet(Dropdown::class);
	}

	/**
	 * Function tryGet
	 * @param string $expected
	 * @return Element|mixed
	 * @internal
	 */
	public function tryGet(string $expected = Element::class){ //why PHP still hasn't templates???
		if (($element = array_shift($this->elements)) instanceof Label) {
			return $this->tryGet($expected); //remove useless element
		} else if ($element === null || !($element instanceof $expected)) {
			throw new FormValidationException("Expected a element with of type $expected, got " . get_class($element));
		}
		return $element;
	}

	/**
	 * Function getInput
	 * @return Input
	 */
	public function getInput(): Input{
		return $this->tryGet(Input::class);
	}

	/**
	 * Function getSlider
	 * @return Slider
	 */
	public function getSlider(): Slider{
		return $this->tryGet(Slider::class);
	}

	/**
	 * Function getStepSlider
	 * @return StepSlider
	 */
	public function getStepSlider(): StepSlider{
		return $this->tryGet(StepSlider::class);
	}

	/**
	 * Function getToggle
	 * @return Toggle
	 */
	public function getToggle(): Toggle{
		return $this->tryGet(Toggle::class);
	}

	/**
	 * Function getElements
	 * @return Element[]
	 */
	public function getElements(): array{
		return $this->elements;
	}

	/**
	 * Function getValues
	 * @return array
	 */
	public function getValues(): array{
		$values = [];
		foreach ($this->elements as $element) {
			if ($element instanceof Label)
				continue;
			$values[] = $element instanceof Dropdown ? $element->getSelectedOption() : $element->getValue();
		}
		return $values;
	}
}