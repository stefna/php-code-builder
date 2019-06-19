<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

class PhpParam
{
	private const NO_VALUE = '__PhpParam_NoValue__';

	/** @var bool  */
	private $allowNull = false;

	/** @var string */
	private $name;

	/** @var string */
	private $type;

	private $complexType;

	private $value;

	public function __construct(string $type, string $name, $value = self::NO_VALUE)
	{
		if ($name[0] === '$') {
			$name = substr($name, 1);
		}
		$this->name = $name;
		$this->value = $value;

		if ($type && $type[0] === '?') {
			$this->allowNull = true;
			$type = substr($type, 1);
		}

		$this->type = $type;
	}

	public function getSource(): string
	{
		$ret = '';
		if ($this->allowNull && $this->type) {
			$ret .= '?';
		}
		$ret .= $this->type ? $this->type . ' ' : '';
		$ret .= '$' . $this->name;
		if ($this->value !== self::NO_VALUE) {
			$ret .= ' = ' . FormatValue::format($this->value);
		}

		return $ret;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function setType(string $type): void
	{
		$this->type = $type;
	}

	public function setValue($value): void
	{
		$this->value = $value;
	}

	public function allowNull(bool $flag): void
	{
		$this->allowNull = $flag;
	}

	public function isNullable(): bool
	{
		return $this->allowNull;
	}

	public function isAllowNull(): bool
	{
		return $this->allowNull;
	}

	/**
	 * @param bool $allowNull
	 */
	public function setAllowNull(bool $allowNull): void
	{
		$this->allowNull = $allowNull;
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
}
