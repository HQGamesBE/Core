<?php
declare(strict_types=1);
namespace HQGames\forms\elements;
use Closure;
use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use HQGames\Core\player\Player;
use JetBrains\PhpStorm\ArrayShape;
use pocketmine\form\FormValidationException;
use pocketmine\utils\Utils;


/**
 * Class Dropdown
 * @package HQGames\forms\elements
 * @author Jan Sohn / xxAROX
 * @date 04. July, 2022 - 23:44
 * @ide PhpStorm
 * @project Core
 */
class Dropdown extends Element{
	/** @var string[] */
	protected array $options;
	protected int $default;
	protected ?Closure $on_submit;

	/**
	 * Dropdown constructor.
	 * @param string $text
	 * @param string[] $options
	 * @param int $default
	 * @param null|Closure $on_submit
	 */
	public function __construct(string $text, array $options, int $default = 0, ?Closure $on_submit = null){
		parent::__construct($text, $default);
		$this->options = $options;
		Utils::validateCallableSignature(new CallbackType(new ReturnType(), new ParameterType("player", Player::class), new ParameterType("element", Element::class)), $on_submit);
		$this->on_submit = $on_submit;
	}

	public function onSubmit(Player $player, self $element): void{
		if ($this->on_submit !== null) ($this->on_submit)($player, $element);
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
	 * @return string
	 */
	public function getType(): string{
		return "dropdown";
	}

	/**
	 * @return array
	 */
	#[ArrayShape([ "options" => "string[]" ])]
	public function serializeElementData(): array{
		return [ "options" => $this->options ];
	}

	public function validate(mixed $value): void{
		parent::validate($value);
		if (!isset($this->options[$value])) {
			throw new FormValidationException("Option with index $value does not exist in dropdown");
		}
	}
}