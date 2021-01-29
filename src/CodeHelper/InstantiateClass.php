<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\Indent;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class InstantiateClass implements CodeInterface
{
	use MethodParamsTrait;

	private $class;

	private $method = '';

	private $indentFirstLine = false;

	public function __construct(Identifier $class, array $params = [])
	{
		$this->identifier = 'new ' . $class->getName();
		$this->callIdentifier = '';
		$this->class = $class;
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
