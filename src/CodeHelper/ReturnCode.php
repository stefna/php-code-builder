<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\Exception\InvalidCode;

final class ReturnCode implements CodeInterface
{
	public function __construct(
		private CodeInterface $code,
	) {}

	public function getSourceArray(): array
	{
		$code = $this->code->getSourceArray();
		if (!is_string($code[0])) {
			throw InvalidCode::invalidType();
		}
		$code[0] = 'return ' . $code[0];
		$lastKey = (int)array_key_last($code);
		if (!is_string($code[$lastKey])) {
			throw InvalidCode::invalidType();
		}
		$code[$lastKey] .= ';';
		return $code;
	}
}
