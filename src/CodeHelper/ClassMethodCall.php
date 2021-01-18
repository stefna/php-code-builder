<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\Indent;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class ClassMethodCall implements CodeInterface
{
	use MethodParamsTrait;

	private $class;

	private $method;

	private $indentFirstLine = false;

	public function __construct(VariableReference $class, string $method, array $params = [])
	{
		$this->identifier = $class->getSource();
		$this->class = $class;
		$this->method = $method;
		$this->params = $params;
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

	public function getSource(int $currentIndent = 0): string
	{
		$indent = ($this->indentFirstLine ? Indent::indent($currentIndent) : '');
		return $indent . FlattenSource::source($this->getSourceArray(), $currentIndent);
	}
}
