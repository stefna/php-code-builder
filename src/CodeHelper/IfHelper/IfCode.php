<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper\IfHelper;

use Stefna\PhpCodeBuilder\CodeHelper\CodeInterface;

final class IfCode implements CodeInterface
{
	/**
	 * @param array<int, mixed> $code
	 */
	public function __construct(
		private string $if,
		private array $code,
		public IfType $type = IfType::If,
	) {}

	public function getSourceArray(int $currentIndent = 0): array
	{
		$ifStmt = $this->type->toStmt();
		if ($this->type !== IfType::Else) {
			$ifStmt .= sprintf(' (%s)', $this->if);
		}
		$ifStmt .= ' {';
		return [
			$ifStmt,
			$this->code,
			'}',
		];
	}
}
