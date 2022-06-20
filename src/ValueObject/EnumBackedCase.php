<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\ValueObject;

class EnumBackedCase extends EnumCase
{
	public function __construct(
		protected string $name,
		protected string $value,
	) {}

	public function getValue(): mixed
	{
		return $this->value;
	}
}
