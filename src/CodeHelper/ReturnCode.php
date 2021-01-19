<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\Indent;

final class ReturnCode implements CodeInterface
{
	private $code;

	public function __construct(CodeInterface $code)
	{
		$this->code = $code;
	}

	public function getSource(int $currentIndent = 0): string
	{
		return Indent::indent($currentIndent) . FlattenSource::source($this->getSourceArray());
	}

	public function getSourceArray(int $currentIndent = 0): array
	{
		$code = $this->code->getSourceArray();
		$code[0] = 'return ' . $code[0];
		$code[count($code) - 1] .= ';';

		return $code;
	}
}
