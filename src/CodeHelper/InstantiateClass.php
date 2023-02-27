<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class InstantiateClass implements CodeInterface, MethodCallInterface
{
	use MethodParamsTrait;

	protected string $method = '';
	protected bool $indentFirstLine = false;

	/**
	 * @param array<int, VariableReference|ArrayCode|string> $params
	 */
	public function __construct(
		protected Identifier $class,
		protected array $params = [],
	) {
		$this->identifier = 'new ' . $class->getName();
		$this->callIdentifier = '';
	}

	/**
	 * @param bool $indentFirstLine
	 */
	public function setIndentFirstLine(bool $indentFirstLine): void
	{
		$this->indentFirstLine = $indentFirstLine;
	}

	/**
	 * @return array<int, mixed>
	 */
	public function getSourceArray(): array
	{
		return $this->buildSourceArray();
	}
}
