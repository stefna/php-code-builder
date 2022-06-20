<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\ValueObject;

class EnumCase
{
	public function __construct(
		protected string $name,
	) {}

	public function getName(): string
	{
		return $this->name;
	}
}
