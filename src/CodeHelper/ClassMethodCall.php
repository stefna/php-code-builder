<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

final class ClassMethodCall implements CodeInterface
{
	use MethodParamsTrait;

	/**
	 * @param array<int, VariableReference|ArrayCode|string> $params
	 */
	public static function this(string $method, array $params = []): self
	{
		return new self(VariableReference::this(), $method, $params);
	}

	/**
	 * @param array<int, VariableReference|ArrayCode|string> $params
	 */
	public function __construct(
		private VariableReference $class,
		private string $method,
		private array $params = []
	) {
		$this->identifier = $class->toString();
	}

	public function getVariableReference(): VariableReference
	{
		return $this->class;
	}

	/**
	 * @return array<int, mixed>
	 */
	public function getSourceArray(): array
	{
		return $this->buildSourceArray();
	}
}
