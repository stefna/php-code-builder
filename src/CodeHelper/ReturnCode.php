<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\Indent;

final class ReturnCode implements CodeInterface
{
	public function __construct(
		private CodeInterface $code,
	) {}

	public function getSourceArray(): array
	{
		$code = $this->code->getSourceArray();
		$code[0] = 'return ' . $code[0];
		$code[count($code) - 1] .= ';';

		return $code;
	}
}
