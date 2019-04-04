<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

class PhpParam
{
	private const NO_VALUE = '__PhpParam_NoValue__';

	/** @var string */
	private $name;
	/** @var string */
	private $type;
	private $value;

	public function __construct(string $type, string $name, $value = self::NO_VALUE)
	{
		$this->name = $name;
		$this->value = $value;
		$this->type = $type;
	}

	public function getSource(): string
	{
		$ret = '';
		$ret .= $this->type ? $this->type . ' ' : '';
		$ret .= '$' . $this->name;
		if ($this->value !== self::NO_VALUE) {
			$value = $this->value;
			switch (gettype($this->value)) {
				case 'boolean':
					$value = $value ? 'true' : 'false';
					break;
				case 'NULL':
					$value = 'null';
					break;
				case 'string':
					$value = "'$value'";
					break;
				case 'array':
					$value = '[]';
					break;
			}

			$ret .= ' = ' . $value;
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
}
