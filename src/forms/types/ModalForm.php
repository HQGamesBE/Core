<?php
/*
 * Copyright (c) Jan Sohn / xxAROX
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */

declare(strict_types=1);
namespace HQGames\forms\types;
use Closure;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

use function gettype;
use function is_bool;


/**
 * Class ModalForm
 * @package HQGames\forms\forms\types
 * @author Jan Sohn / xxAROX
 * @date 05. July, 2022 - 00:01
 * @ide PhpStorm
 * @project Core
 */
class ModalForm extends Form{
	protected string $text;
	private string $yesButton;
	private string $noButton;
	private Closure $onSubmit;

	/**
	 * ModalForm constructor.
	 * @param string $title
	 * @param string $text
	 * @param Closure $onSubmit (Player $player, bool $yes): void
	 * @param string $yesButton
	 * @param string $noButton
	 */
	#[Pure]
	public function __construct(string $title, string $text, Closure $onSubmit, string $yesButton = "%forms.yes", string $noButton = "%forms.no"){
		parent::__construct($title);
		$this->text = $text;
		$this->yesButton = $yesButton;
		$this->noButton = $noButton;
		$this->onSubmit = $onSubmit;
	}

	/**
	 * Function getType
	 * @return string
	 */
	final public function getType(): string{
		return self::TYPE_MODAL;
	}

	/**
	 * Function getYesButtonText
	 * @return string
	 */
	public function getYesButtonText(): string{
		return $this->yesButton;
	}

	/**
	 * Function getNoButtonText
	 * @return string
	 */
	public function getNoButtonText(): string{
		return $this->noButton;
	}

	/**
	 * Function handleResponse
	 * @param Player $player
	 * @param mixed $data
	 * @return void
	 */
	final public function handleResponse(Player $player, $data): void{
		if (!is_bool($data))
			throw new FormValidationException("Expected bool, got " . gettype($data));
		($this->onSubmit)($player, $data);
	}

	/**
	 * Function serializeFormData
	 * @return array
	 */
	#[ArrayShape([
		"content" => "string",
		"button1" => "string",
		"button2" => "string",
	])] protected function serializeFormData(): array{
		return [
			"content" => $this->text,
			"button1" => $this->yesButton,
			"button2" => $this->noButton,
		];
	}
}