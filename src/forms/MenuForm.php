<?php
declare(strict_types=1);
namespace HQGames\Core\forms;
use Closure;
use HQGames\Core\forms\elements\Button;
use HQGames\Core\forms\elements\FunctionalButton;
use JetBrains\PhpStorm\ArrayShape;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;


/**
 * Class MenuForm
 * @package HQGames\Core\forms
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:59
 * @ide PhpStorm
 * @project Core
 */
class MenuForm extends Form{
	/** @var Button[] */
	protected array $buttons = [];
	protected string $text;
	private ?Closure $onSubmit = null;
	private ?Closure $onClose = null;

	/**
	 * MenuForm constructor.
	 * @param string $title
	 * @param string $text
	 * @param Button[]|string[] $buttons
	 * @param Closure|null $onSubmit
	 * @param Closure|null $onClose
	 */
	public function __construct(string $title, string $text = "", array $buttons = [], ?Closure $onSubmit = null, ?Closure $onClose = null){
		parent::__construct($title);
		$this->text = $text;
		foreach ($buttons as $k => $button) {
			if (is_null($button)) {
				unset($this->buttons[$k]);
			} else if (is_string($button)) {
				$this->buttons[] = new Button($button);
			} else if ($button instanceof Button) {
				$this->buttons[] = $button;
			}
		}
		$this->setOnSubmit($onSubmit);
		$this->setOnClose($onClose);
	}

	/**
	 * Function setOnSubmit
	 * @param null|Closure $onSubmit
	 * @return $this
	 */
	public function setOnSubmit(?Closure $onSubmit): self{
		if ($onSubmit !== null)
			$this->onSubmit = $onSubmit;
		return $this;
	}

	/**
	 * Function setOnClose
	 * @param null|Closure $onClose
	 * @return $this
	 */
	public function setOnClose(?Closure $onClose): self{
		if ($onClose !== null)
			$this->onClose = $onClose;
		return $this;
	}

	/**
	 * Function setText
	 * @param string $text
	 * @return $this
	 */
	public function setText(string $text): self{
		$this->text = $text;
		return $this;
	}

	/**
	 * Function getType
	 * @return string
	 */
	final public function getType(): string{
		return self::TYPE_MENU;
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
				($this->onClose)($player, $data);
		} else if (is_int($data)) {
			if (!isset($this->buttons[$data]))
				throw new FormValidationException("Button with index $data does not exist");
			$button = $this->buttons[$data];
			if ($this->onSubmit !== null) {
				$button->setValue($data);
				($this->onSubmit)($player, $button);
			} else {
				if ($button instanceof FunctionalButton)
					$button->onClick($player);
			}
		} else {
			throw new FormValidationException("Expected int or null, got " . gettype($data));
		}
	}

	/**
	 * Function serializeFormData
	 * @return array
	 */
	#[ArrayShape([
		"buttons" => "array|\HQGames\Core\forms\elements\Button[]",
		"content" => "string",
	])] protected function serializeFormData(): array{
		return [
			"buttons" => array_values($this->buttons),
			"content" => $this->text,
		];
	}
}