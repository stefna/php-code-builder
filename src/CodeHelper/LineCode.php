<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;

final class LineCode implements CodeInterface
{
	private $code;

	public function __construct(CodeInterface $code)
	{
		$this->code = $code;
	}

	public function getSource(int $currentIndent = 0): string
	{
		return FlattenSource::source($this->getSourceArray($currentIndent), $currentIndent);
	}

	public function getSourceArray(int $currentIndent = 0): array
	{
		$code = $this->code->getSourceArray($currentIndent);
		$code[count($code) - 1] .= ';';
		return $code;
	}
}
