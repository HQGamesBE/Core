<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\forms\types;
use Closure;
use HQGames\forms\elements\Element;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;


/**
 * Class CustomForm
 * @package HQGames\forms\types
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:52
 * @ide PhpStorm
 * @project Core
 */
class CustomForm extends Form{
	/** @var Element[] */
	protected array $elements;
	private Closure $onSubmit;
	private ?Closure $onClose;

	/**
	 * CustomForm constructor.
	 * @param string $title
	 * @param array $elements
	 * @param Closure $onSubmit after all elements are called
	 * @param null|Closure $onClose
	 */
	#[Pure]
	public function __construct(string $title, array $elements, Closure $onSubmit, ?Closure $onClose = null){
		parent::__construct($title);
		$this->elements = $elements;
		$this->onSubmit = $onSubmit;
		$this->onClose = $onClose;
		$this->onSubmit = $onSubmit;
	}

	/**
	 * Function append
	 * @param Element ...$elements
	 * @return $this
	 */
	public function append(Element ...$elements): self{
		$this->elements = array_merge($this->elements, $elements);
		return $this;
	}

	/**
	 * Function getType
	 * @return string
	 */
	final public function getType(): string{
		return self::TYPE_CUSTOM_FORM;
	}

	/**
	 * Function handleResponse
	 * @param Player $player
	 * @param mixed $data
	 * @return void
	 */
	final public function handleResponse(Player $player, $data): void{
		if ($data === null) {
			if ($this->onClose !== null)
				($this->onClose)($player);
		} else if (is_array($data)) {
			foreach ($data as $index => $value) {
				if (!isset($this->elements[$index]))
					throw new FormValidationException("Element at index $index does not exist");
				$element = $this->elements[$index];
				$element->validate($value);
				$element->setValue($value);
				/** @var \HQGames\Core\player\Player $player */
				if (method_exists($element, "onSubmit"))
					$element->onSubmit($player, $element);
			}
			($this->onSubmit)($player, new CustomFormResponse($this->elements));
		} else throw new FormValidationException("Expected array or null, got " . gettype($data));
	}

	/**
	 * Function serializeFormData
	 * @return array
	 */
	#[ArrayShape(["content" => "array|\\HQGames\\Core\\forms\\elements\\Element[]"])] protected function serializeFormData(): array{
		return ["content" => $this->elements];
	}
}