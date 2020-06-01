<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

class PhpConstant extends PhpElement
{
	public const CASE_UPPER = 0;
	public const CASE_LOWER = 1;
	public const CASE_NONE = 2;

	/** @var string */
	private $value;
	/** @var bool */
	private $rawValue = false;
	/** @var int */
	private $case;

	public static function public(string $identifier, $value = null): self
	{
		return new self(self::PUBLIC_ACCESS, $identifier, $value);
	}

	public static function private(string $identifier, $value = null): self
	{
		return new self(self::PRIVATE_ACCESS, $identifier, $value);
	}

	public static function protected(string $identifier, $value = null): self
	{
		return new self(self::PROTECTED_ACCESS, $identifier, $value);
	}

	public function __construct(
		string $access,
		string $identifier,
		?string $value = null,
		int $case = 0
	) {
		$this->access = $access;
		$this->identifier = $identifier;
		if ($value === null) {
			$value = $identifier;
		}
		$this->value = $value;
		$this->case = $case;
	}

	/**
	 * Returns the complete source code for the variable
	 *
	 * @return string
	 */
	public function getSource(): string
	{
		$ret = '';
		$ret .= $this->access ? $this->access . ' ' : '';

		$name = $this->getName();
		$value = $this->getFormattedValue();
		$ret .= "const $name = {$value};";

		return $this->getSourceRow($ret);
	}

	public function getName(): string
	{
		$name = $this->identifier;
		$name = preg_replace('/^(\d)/', '_$0', $name);
		$name = str_replace('-', '_', $name);

		switch ($this->case) {
			case self::CASE_UPPER:
			case self::CASE_LOWER:
				$name = preg_replace('/(?<!^)[A-Z]/', '_$0', $name);
			//Use this to make sure name are readable
			case self::CASE_UPPER:
				$name = strtoupper($name);
				break;
			case self::CASE_LOWER:
				$name = strtolower($name);
				break;
		}

		return $name;
	}

	public function setValue(string $value): self
	{
		$this->rawValue = false;
		$this->value = $value;
		return $this;
	}

	public function setRawValue(string $value): self
	{
		$this->rawValue = true;
		$this->value = $value;
		return $this;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function getFormattedValue(): string
	{
		return $this->rawValue ? $this->value : FormatValue::format($this->value);
	}

	public function setCase(int $case)
	{
		$this->case = $case;
		return $this;
	}
}
