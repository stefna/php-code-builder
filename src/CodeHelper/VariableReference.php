<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

final class VariableReference implements CodeInterface
{
	public static function this(string $variable = ''): self
	{
		if ($variable) {
			return new self('this->' . $variable);
		}
		return new self('this');
	}

	public function __construct(
		private string $name
	) {}

	public function toString(): string
	{
		return '$' . $this->name;
	}

	public function getSourceArray(): array
	{
		return ['$' . $this->name];
	}
}
