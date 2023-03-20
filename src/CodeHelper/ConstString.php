<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class ConstString implements CodeInterface
{
	public static function class(Identifier|string $classString): self
	{
		$identifier = Identifier::fromUnknown($classString);
		return new self($identifier->toString() . '::class');
	}

	public function __construct(
		private string $string,
	) {}

	public function toString(): string
	{
		return $this->string;
	}

	/**
	 * @return string[]
	 */
	public function getSourceArray(): array
	{
		return [$this->string];
	}
}
