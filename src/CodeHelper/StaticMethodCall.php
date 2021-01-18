<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\FormatValue;
use Stefna\PhpCodeBuilder\Indent;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class StaticMethodCall implements CodeInterface
{
	use MethodParamsTrait;

	private $class;

	private $method;

	private $indentFirstLine = false;

	public function __construct(Identifier $class, string $method, array $params = [])
	{
		$this->identifier = $class->getName();
		$this->callIdentifier = '::';
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
