<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

class PhpConstant extends PhpElement
{
	public const CASE_UPPER = 0;
	public const CASE_LOWER = 1;
	public const CASE_NONE = 2;

	/** @var string */
	private $value;
	/** @var int */
	private $case;

	public function __construct(
		string $access,
		string $identifier,
		string $value = null,
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

		$name = $this->identifier;
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

		$value = FormatValue::format($this->value);
		$ret .= "const $name = {$value};";

		return $this->getSourceRow($ret);
	}

	public function setValue(string $value): self
	{
		$this->value = $value;
		return $this;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function setCase(int $case)
	{
		$this->case = $case;
		return $this;
	}
}
