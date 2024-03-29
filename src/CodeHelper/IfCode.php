<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class IfCode implements CodeInterface
{
	/**
	 * @param array<int, mixed> $code
	 * @param Identifier|class-string $identifier
	 */
	public static function instanceOf(VariableReference $var, Identifier|string $identifier, array $code): self
	{
		$identifier = Identifier::fromUnknown($identifier);
		return new self(
			$var->toString() . ' instanceof ' . $identifier->getName(),
			$code
		);
	}

	/**
	 * @param array<int, mixed> $code
	 */
	public static function nullCheck(VariableReference $var, array $code): self
	{
		return new self(
			$var->toString() . ' === null',
			$code
		);
	}

	/**
	 * @param array<int, mixed> $code
	 */
	public function __construct(
		private string $if,
		private array $code,
	) {}

	public function getSourceArray(int $currentIndent = 0): array
	{
		return [
			sprintf('if (%s) {', $this->if),
			$this->code,
			'}',
		];
	}
}
