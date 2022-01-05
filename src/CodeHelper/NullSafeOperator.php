<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

final class NullSafeOperator implements CodeInterface
{
	/**
	 * @param list<VariableReference|ArrayCode|string> $params
	 */
	public static function create(VariableReference $reference, string $call, array $params = []): self
	{
		return new self(new ClassMethodCall($reference, $call, $params));
	}

	public function __construct(
		private ClassMethodCall $call,
	) {}

	public function getSourceArray(): array
	{
		$source = $this->call->getSourceArray();
		$reference = $this->call->getVariableReference()->toString();
		$source[0] = str_replace($reference . '->', $reference . '?->', $source[0]);
		return $source;
	}
}
