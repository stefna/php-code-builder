<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper\IfHelper;

use Stefna\PhpCodeBuilder\CodeHelper\CodeInterface;

/**
 * @implements \ArrayAccess<int, IfCode>
 */
final class IfCollection implements CodeInterface, \ArrayAccess
{
	/** @var list<IfCode> */
	private array $checks = [];

	private ?IfCode $else = null;

	public function getSourceArray(): array
	{
		$result = [];
		foreach ($this->checks as $index => $check) {
			$check->type = $index === 0 ? IfType::If : IfType::ElseIf;
			$result[] = $check->getSourceArray();
		}
		if ($this->else) {
			$result[] = $this->else->getSourceArray();
		}

		return array_merge(...$result);
	}

	/**
	 * @param array<int, mixed> $code
	 */
	public function addElse(array $code): void
	{
		$this->else = new IfCode(
			'',
			$code,
			IfType::Else,
		);
	}

	public function offsetExists(mixed $offset): bool
	{
		return true;
	}

	public function offsetGet(mixed $offset): mixed
	{
		return null;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (!$value instanceof IfCode) {
			throw new \ValueError('Value must be instance of IfCode, got ' . get_debug_type($value));
		}
		$this->checks[] = $value;
	}

	public function offsetUnset(mixed $offset): void
	{
	}
}
