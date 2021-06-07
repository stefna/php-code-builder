<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class IfCode implements CodeInterface
{
	/**
	 * @param array<int, mixed> $code
	 */
	public static function instanceOf(VariableReference $var, Identifier $identifier, array $code): self
	{
		return new self(
			$var->toString() . ' instanceof ' . $identifier->getName(),
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

	/**
	 * @return array<int,string|string[]>
	 */
	public function getSourceArray(int $currentIndent = 0): array
	{
		return [
			sprintf('if (%s) {', $this->if),
			$this->code,
			'}',
		];
	}
}
