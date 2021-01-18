<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

final class VariableReference implements CodeInterface
{
	private $name;

	public static function this(): self
	{
		return new self('this');
	}

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function getSource(int $currentIndent = 0): string
	{
		return '$' . $this->name;
	}

	public function getSourceArray(int $currentIndent = 0): array
	{
		return ['$' . $this->name];
	}
}
