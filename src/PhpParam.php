<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\Type;

class PhpParam
{
	private const NO_VALUE = '__PhpParam_NoValue__';

	/** @var string */
	private $name;

	/** @var Type */
	private $type;

	private $complexType;

	private $value;

	/**
	 * Optional variable connected to param
	 *
	 * @var PhpVariable|null
	 */
	private $variable;

	public static function fromVariable(PhpVariable $var): self
	{
		$self = new static($var->getIdentifier()->getName(), $var->getType());
		$self->variable = $var;
		return $self;
	}

	public function __construct(string $name, Type $type, $value = self::NO_VALUE)
	{
		if (strpos($name, '$') === 0) {
			$name = substr($name, 1);
		}
		$this->name = $name;
		$this->value = $value;
		$this->type = $type;
	}

	public function getVariable(): ?PhpVariable
	{
		return $this->variable;
	}

	public function getSource(): string
	{
		$ret = '';
		if ($this->type->getTypeHint()) {
			$ret .= $this->type->getTypeHint();
		}
		$ret .= ' $' . $this->name;
		if ($this->value !== self::NO_VALUE) {
			$ret .= ' = ' . FormatValue::format($this->value);
		}

		return trim($ret);
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function setType(Type $type): void
	{
		$this->type = $type;
	}

	public function setValue($value): void
	{
		$this->value = $value;
	}

	public function allowNull(): void
	{
		$this->type->addUnion('null');
	}

	public function isNullable(): bool
	{
		return $this->type->isNullable();
	}

	/**
	 * Get complex return type hinting like arrays and constants
	 */
	public function getComplexType(): ?string
	{
		return $this->complexType;
	}

	/**
	 * Set complex return typehint information
	 */
	public function setComplexType(string $complexType): void
	{
		$this->complexType = $complexType;
	}

	public function __clone()
	{
		$this->type = clone $this->type;
	}
}
