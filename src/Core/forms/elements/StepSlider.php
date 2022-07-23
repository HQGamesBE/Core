<?php
/*
 * Copyright (c) 2021 Jan Sohn.
 * All rights reserved.
 * I don't want anyone to use my source code without permission.
 */
declare(strict_types=1);
namespace HQGames\Core\forms\elements;
use JetBrains\PhpStorm\ArrayShape;


/**
 * Class StepSlider
 * @package HQGames\Core\forms\elements
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:50
 * @ide PhpStorm
 * @project Core
 */
class StepSlider extends Dropdown{
	/**
	 * Function getType
	 * @return string
	 */
	public function getType(): string{
		return "step_slider";
	}

	/**
	 * Function serializeElementData
	 * @return array
	 */
	#[ArrayShape(["steps" => "string[]", "default" => "int"])] public function serializeElementData(): array{
		return [
			"steps"   => $this->options,
			"default" => $this->default,
		];
	}
}