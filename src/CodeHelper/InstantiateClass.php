<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\Indent;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class InstantiateClass implements CodeInterface
{
	use MethodParamsTrait;

	private string $method = '';
	private bool $indentFirstLine = false;

	public function __construct(
		private Identifier $class,
		private array $params = [],
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

	public function getSourceArray(int $currentIndent = 0): array
	{
		return $this->buildSourceArray($currentIndent);
	}
}
