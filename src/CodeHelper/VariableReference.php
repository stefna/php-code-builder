<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

final class VariableReference implements CodeInterface
{
	public static function array(string $variable, string |VariableReference $arrayKey): self
	{
		$key = is_string($arrayKey) ? "'$arrayKey'" : $arrayKey->toString();
		return new self($variable . "[$key]");
	}

	public static function this(string $variable = ''): self
	{
		if ($variable) {
			return new self($variable, 'this->');
		}
		return new self('this');
	}

	public function __construct(
		private string $name,
		private string $prefix = '',
	) {}

	public function toString(): string
	{
		return '$' . $this->prefix . ltrim($this->name, '$');
	}

	public function getName(): string
	{
		return ltrim($this->name, '$');
	}

	/**
	 * @return string[]
	 */
	public function getSourceArray(): array
	{
		return ['$' . $this->prefix . ltrim($this->name, '$')];
	}
}
