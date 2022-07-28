<?php
declare(strict_types=1);
namespace HQGames\forms\elements;
use Closure;
use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use HQGames\Core\player\Player;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use pocketmine\form\FormValidationException;
use pocketmine\utils\Utils;


/**
 * Class Input
 * @package HQGames\forms\elements
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:44
 * @ide PhpStorm
 * @project Core
 */
class Input extends Element{
	protected string $placeholder;
	protected string $default;
	protected ?Closure $on_submit;

	/**
	 * Input constructor.
	 * @param string $text
	 * @param string $placeholder
	 * @param string $default
	 * @param null|Closure $on_submit
	 */
	public function __construct(string $text, string $placeholder = "", string $default = "", ?Closure $on_submit = null){
		parent::__construct($text, $default);
		$this->placeholder = $placeholder;
		Utils::validateCallableSignature(new CallbackType(
			new ReturnType(),
			new ParameterType("player", Player::class),
			new ParameterType("element", Element::class)
		), $on_submit);
		$this->on_submit = $on_submit;
	}

	public function onSubmit(Player $player, self $element): void{
		if ($this->on_submit !== null) ($this->on_submit)($player, $element);
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
	#[ArrayShape(["placeholder" => "string"])]
	public function serializeElementData(): array{
		return ["placeholder" => $this->placeholder ];
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