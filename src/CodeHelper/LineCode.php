<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

final class LineCode implements CodeInterface
{
	public function __construct(
		private CodeInterface $code,
	) {}

	/**
	 * @return array<int,string|string[]>
	 */
	public function getSourceArray(): array
	{
		$code = $this->code->getSourceArray();
		$lastIndex = count($code) - 1;
		if (is_array($code[$lastIndex])) {
			$code[$lastIndex][count($code[$lastIndex]) - 1] .= ';';
		}
		else {
			$code[$lastIndex] .= ';';
		}
		return $code;
	}
}
