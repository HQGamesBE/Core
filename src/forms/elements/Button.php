<?php
declare(strict_types=1);
namespace HQGames\Core\forms\elements;
use JetBrains\PhpStorm\Pure;


/**
 * Class Button
 * @package HQGames\Core\forms
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:43
 * @ide PhpStorm
 * @project Core
 */
class Button extends Element{
	protected ?Image $image;
	protected string $type;

	/**
	 * @param string $text
	 * @param Image|null $image
	 */
	#[Pure] public function __construct(string $text, ?Image $image = null){
		parent::__construct($text);
		$this->image = $image;
	}

	/**
	 * @param string ...$texts
	 *
	 * @return Button[]
	 */
	public static function createFromList(string ...$texts): array{
		$buttons = [];
		foreach ($texts as $text) {
			$buttons[] = new self($text);
		}
		return $buttons;
	}

	/**
	 * @return string|null
	 */
	public function getType(): ?string{
		return null;
	}

	/**
	 * @return array
	 */
	public function serializeElementData(): array{
		$data = ["text" => $this->text];
		if ($this->hasImage()) {
			$data["image"] = $this->image;
		}
		return $data;
	}

	/**
	 * @return bool
	 */
	public function hasImage(): bool{
		return $this->image !== null;
	}
}