<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

interface CodeInterface
{
	/**
	 * @return array<int,string|string[]>
	 */
	public function getSourceArray(): array;
}
